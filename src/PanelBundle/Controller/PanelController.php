<?php

namespace PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use AppBundle\Form\{RoomType,UserType};
use AppBundle\Entity\{Words,Room,RoomNotes,User,Plan,NoteFiles};

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\HttpFoundation\JsonResponse;
use UserBundle\Twig\AppExtension;

/**
* @Route("/panel")
*/

class PanelController extends Controller
{
    /**
     * @Route("/", name="panel")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return;
    }

    /**
     * @Route("/files", name="panel_files")
     * @Template
     */
    public function filesAction(Request $request)
    {
        return;
    }

    /**
     * @Route("/room", name="panel_room")
     * @Template
     */
    public function roomAction(Request $request)
    {
        $my_rooms = $this->getUser()->getRooms();
        return ['rooms' => $my_rooms];
    }

    /**
     * @Route("/room/create", name="panel_room_create")
     * @Template
     */
    public function roomCreateAction(Request $request)
    {
        if (count($this->getUser()->getRooms()) >= $this->getUser()->getPlan()->getMaxRooms()) {
            $this->addFlash(
                'info',
                'Upgrade your plan to create more rooms <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.'
            );
            $this->addFlash(
                'danger',
                'You have reached the room limit '.$this->getUser()->getPlan()->getMaxRooms().'.'
            );
            return $this->redirectToRoute('panel_room');
        }
        $em = $this->getDoctrine()->getManager();
        $words = $em->getRepository(Words::class);
        $totalWords = $words->createQueryBuilder('a')
            ->getQuery()
            ->getResult();
        $ile = 0;
        do {
            $url = $totalWords[$ile]->getWord();
            $ile++;
        } while ($em->getRepository(Room::class)->findOneByUrl($url) !== null && $ile < count($totalWords));
        if ($em->getRepository(Room::class)->findOneByUrl($url) !== null)
            $url = '';

        $room = new Room();
        $room->setUrl($url);
        $room->setCreated(new \DateTime('now'));
        $room->setEdited(new \DateTime('now'));
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $room = $form->getData();
            if($room->getPassword() !== null){
                $encoder = $this->get('security.encoder_factory')->getEncoder(Room::class);
                $encodedPassword = $encoder->encodePassword($room->getPassword(), $room);
                $room->setPassword($encodedPassword);
            }
            if($this->isGranted('ROLE_USER')){
                $room->setUser($this->getUser());
            } else {
                $session = $this->get('session');
                $session->set('control_url', $room->getUrl());
                $session->set('ask_join', true);
            }
            // echo var_dump($encoder->isPasswordValid($room->getPassword(), $pass, $room));
            $em->persist($room);
            $roomNotes = new RoomNotes();
            $roomNotes->setUser($this->getUser());
            $roomNotes->setRoom($room);
            $em->persist($roomNotes);
            $em->flush();
            
            // return $this->redirectToRoute('panel_room_url', ['url' => $room->getUrl()]);
            return $this->redirectToRoute('panel_room');
        }
        return ['form' => $form->createView(), 'errors' => $form->getErrors()];
    }

    /**
     * @Route("/room/{url}/remove", name="panel_room_clear")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function roomClearAction(Request $request, Room $room = null)
    {
        if($room !== null)
            if ($this->isMyRoom($room)) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($room);
                $em->flush();
            }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/guest/access", name="panel_room_guest_access_negate")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function gstAcsNegAction(Request $request, Room $room = null)
    {
        if($room !== null)
            if ($this->isMyRoom($room)){
                $em = $this->getDoctrine()->getManager();
                $room->setGuestAccess(!$room->getGuestAccess());
                $em->flush();
            }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/guest/edit", name="panel_room_guest_edit_negate")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function gstEditNegAction(Request $request, Room $room = null)
    {
        if($room !== null)
            if ($this->isMyRoom($room)){
                $em = $this->getDoctrine()->getManager();
                $room->setGuestEdit(!$room->getGuestEdit());
                $em->flush();
            }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/change-public", name="panel_room_change_public")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function changePublicAction(Request $request, Room $room = null)
    {
        if($room !== null)
            if ($this->isMyRoom($room))
                if($this->getUser()->getPlan()->getIsPublic() || $room->getPublic()){
                    $em = $this->getDoctrine()->getManager();
                    $room->setPublic(!$room->getPublic());
                    $em->flush();
                } else {
                    $this->addFlash(
                        'info',
                        'Upgrade your plan to access this feature <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.'
                    );
                    $this->addFlash(
                        'danger',
                        'Your '.$this->getUser()->getPlan()->getName().' plan can&apos;t publish a room.'
                    );
                }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/change-status", name="panel_room_change_status")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function changeStatusAction(Request $request, Room $room = null)
    {
        if($room !== null)
            if ($this->isMyRoom($room)){
                $em = $this->getDoctrine()->getManager();
                $room->setDisabled(!$room->getDisabled());
                $em->flush();
            }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/upgrade", name="panel_upgrade")
     * @Template
     */
    public function upgradeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $plans = $em->getRepository(Plan::class)->findAll();
        return ['plans' => $plans];
    }

    /**
     * @Route("/get-plan/{plan}", name="panel_getPlan")
     * @ParamConverter("plan", class="AppBundle:Plan")
     */
    public function getPlanAction(Request $request, Plan $plan)
    {
        // $em = $this->getDoctrine()->getManager();
        // $this->getUser()->setPlan($plan);
        // $em->flush();
        return $this->redirectToRoute('panel_upgrade');
    }

    /**
     * @Route("/unsubscribe", name="panel_unsubscribe")
     */
    public function unsubscribeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->getUser()->setPlan($em->getRepository(Plan::class)->find(1));
        $em->flush();
        return $this->redirectToRoute('panel_upgrade');
    }

    /**
     * @Route("/search-for-rooms", name="search_for_rooms")
     */
    public function updateRoomAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $message = [];
            $status = false;
            $value = $request->request->get('value', null);
            if(!empty($value) && $value != ""){
                $em = $this->getDoctrine()->getManager();
                $twigExt = $this->container->get('twig')->getExtension(AppExtension::class);
                $roomsRaw = $em->getRepository(Room::class)->findPublicRoomsByName($value, $this->getUser());
                foreach ($roomsRaw as &$value) {
                    $value = [
                        'name' => $value->getName(),
                        'url' => $value->getUrl(),
                        'author' => $twigExt->fullName($value->getUser()),
                        'edited' => $value->getEdited()->format('H:i:s d-m-Y'),
                        'your' => $value->getUser() == $this->getUser() ? true : false
                    ];
                }
                $status = true;
            } else {
                array_push($message, ['warning', "Room name is empty."]);
            }
            $arrData = ['status' => $status,'messages' => $message, 'rooms' => $roomsRaw ?? []];
            return new JsonResponse($arrData);
        } else {
            return $this->redirectToRoute('panel');
        }
    }
    
    /**
     * @Route("/remove-file/{NoteFiles}", name="room_file_remove_in_panel")
     * @ParamConverter("NoteFiles", class="AppBundle:NoteFiles")
     */
    public function removeFileAction(Request $request, NoteFiles $NoteFiles = null)
    {
        $em = $this->getDoctrine()->getManager();
        $status = false;
        $message = [];
        if ($request->isXmlHttpRequest() && $NoteFiles !== null) {
            if($NoteFiles->getUser() != $this->getUser() && $room->getUser() != $this->getUser()){
                array_push($message, ['danger', "This is not your file"]);
            } elseif ($NoteFiles->getRoom()->getDisabled()) {
                array_push($message, ['danger', "Room \"".$NoteFiles->getRoom()>getName()."\" is disabled"]);
            } else {
                $em->remove($NoteFiles);
                $em->flush();
                $status = true;
            }
            $arrData = ['status' => $status,'messages' => $message];
            return new JsonResponse($arrData);
        } else
        return $this->redirectToRoute('panel_room');
    }

    private function isMyRoom(Room $room = null)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) 
            if ($this->getUser() === $room->getUser())
                return true;
        return false;
    }
}
