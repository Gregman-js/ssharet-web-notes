<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;

use AppBundle\Form\RoomType;
use AppBundle\Entity\Words;
use AppBundle\Entity\{Room,RoomNotes};

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Session\Session;

class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->redirectToRoute('panel');
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
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $room = $form->getData();
            if($em->getRepository(Room::class)->findOneByUrl($room->getUrl()) === null) {
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
                // $roomNotes = new RoomNotes();
                $em->flush();
                
                if($this->isGranted('ROLE_USER'))
                    return $this->redirectToRoute('panel_room_url', ['url' => $room->getUrl()]);
                else
                    return $this->redirectToRoute('room_url', ['url' => $room->getUrl()]);
            } else {
                $form->get('url')->addError(new FormError('This url is used'));
            }
        }
        return ['form' => $form->createView(), 'errors' => $form->getErrors()];
    }

    /**
     * @Route("/join", name="please_log_in")
     * @Template
     */
    public function joinAction(Request $request)
    {
        $session = $this->get('session');
        // set and get session attributes
        // $session->set('room_url', 'Drak');
        // $session->get('room_url');
        return;
    }
}
