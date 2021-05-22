<?php

namespace PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use AppBundle\Form\{RoomType,UserType,NoteFilesType};
use AppBundle\Entity\{Words,Room,RoomNotes,RoomMember,NoteFiles,User,Plan};

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class RoomController extends Controller
{
    /**
     * @Route("/room/{url}/remove-member", name="room_member_remove")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function removeMemberAction(Request $request, Room $room = null)
    {
        $em = $this->getDoctrine()->getManager();
        $status = false;
        $message = [];
        if ($request->isXmlHttpRequest()) {
            $name = $request->request->get('username');
            if(!$this->isMyRoom($room)){
                array_push($message, ['danger', "This is not your room"]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
            } elseif ($name) {
                $user = $em->getRepository(User::class)->findOneByUsername($name);
                if ($user !== null) {
                    $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $user, 'room' => $room]);
                    if ($roomMember !== null) {
                        $em->remove($roomMember);
                        $em->flush();
                    }
                }
                $status = true;
            }
            $arrData = ['status' => $status,'messages' => $message];
            return new JsonResponse($arrData);
        } elseif ($roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room])) {
            $em->remove($roomMember);
            $em->flush();
            $status = true;
            return $this->redirectToRoute('panel_room');
        } else
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/remove-file/{NoteFiles}", name="room_file_remove")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     * @ParamConverter("NoteFiles", class="AppBundle:NoteFiles")
     */
    public function removeFileAction(Request $request, Room $room = null, NoteFiles $NoteFiles = null)
    {
        $em = $this->getDoctrine()->getManager();
        $status = false;
        $message = [];
        if ($request->isXmlHttpRequest() && $NoteFiles !== null) {
            if($NoteFiles->getUser() != $this->getUser() && $room->getUser() != $this->getUser()){
                array_push($message, ['danger', "This is not your file"]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
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

    /**
     * @Route("/panel/membership/{roomMember}/approve", name="room_member_approve")
     * @ParamConverter("roomMember", class="AppBundle:RoomMember")
     */
    public function approveMemberAction(Request $request, RoomMember $roomMember = null)
    {
        if ($roomMember !== null) {
            if($roomMember->getUser() != $this->getUser()){
                $this->addFlash(
                    'danger',
                    'It\'s not your membership.'
                );
            } else {
                $em = $this->getDoctrine()->getManager();
                $roomMember->setStatus(RoomMember::$STATUS_APPROVE);
                $roomMember->setReciveDate(new \DateTime("now"));
                $em->flush();
            }
        }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/panel/membership/{roomMember}/deny", name="room_member_deny")
     * @ParamConverter("roomMember", class="AppBundle:RoomMember")
     */
    public function denyMemberAction(Request $request, RoomMember $roomMember = null)
    {
        if ($roomMember !== null) {
            if($roomMember->getUser() != $this->getUser()){
                $this->addFlash(
                    'danger',
                    'It\'s not your membership.'
                );
            } else {
                $em = $this->getDoctrine()->getManager();
                $roomMember->setStatus(RoomMember::$STATUS_REFUSE);
                $roomMember->setReciveDate(new \DateTime("now"));
                $em->flush();
            }
        }
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/add-member", name="room_member_add")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function addMemberAction(Request $request, Room $room = null)
    {
        if ($request->isXmlHttpRequest()) {
            $status = false;
            $message = [];
            $name = $request->request->get('username');
            if(!$this->isMyRoom($room)){
                array_push($message, ['danger', "This is not your room"]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
            } elseif ($name) {
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->findOneByUsername($name);
                if ($user !== null && $user != $room->getUser() && $user != $this->getUser()) {
                    $roomMember = new RoomMember();
                    $roomMember->setSendDate(new \DateTime("now"));
                    $roomMember->setRoom($room);
                    $roomMember->setUser($user);
                    $roomMember->setStatus(RoomMember::$STATUS_SEND);
                    $em->persist($roomMember);
                    $em->flush();
                }
                $status = true;
            }
            $arrData = ['status' => $status,'messages' => $message];
            return new JsonResponse($arrData);
        } else
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/panel/membership/{roomMember}/change-member-permission", name="change_member_permission")
     * @ParamConverter("roomMember", class="AppBundle:RoomMember")
     */
    public function changeMemberPermissionAction(Request $request, RoomMember $roomMember = null)
    {
        if ($roomMember !== null) {
            $status = false;
            $message = [];
            if(!$this->isMyRoom($roomMember->getRoom())){
                array_push($message, ['danger', "This is not your room"]);
            } elseif ($roomMember->getRoom()->getDisabled()) {
                array_push($message, ['danger', "Room \"".$roomMember->getRoom()->getName()."\" is disabled"]);
            } else {
                $em = $this->getDoctrine()->getManager();
                $roomMember->setWriteRight(!$roomMember->getWriteRight());
                $em->flush();
                $status = true;
            }
        }
        return $this->redirectToRoute('room_url', ['url' => $roomMember ? $roomMember->getRoom()->getUrl() : null]);
    }
    
    /**
     * @Route("/room/{url}/{roomNotes}/remove", name="room_note_remove")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     * @ParamConverter("roomNotes", class="AppBundle:RoomNotes")
     */
    public function removeNoteAction(Request $request, Room $room = null, RoomNotes $roomNotes = null)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $status = false;
            $message = [];
            $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
            if(!$this->isMyNote($roomNotes) && !$this->isMyRoom($room)){
                array_push($message, ['danger', "This is not your note"]);
            } elseif ($roomMember !== null && $roomMember->getWriteRight() == false) {
                array_push($message, ['danger', "You do not have permission for this action"]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
            } else {
                $em->remove($roomNotes);
                $em->flush();
                $status = true;
            }
            $arrData = ['status' => $status,'messages' => $message];
            return new JsonResponse($arrData);
        } else
        return $this->redirectToRoute('panel_room');
    }

    /**
     * @Route("/room/{url}/new-note", name="room_note_new")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function newNoteAction(Request $request, Room $room = null)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $message = [];
            $status = false;
            $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
            if(!$this->isMyRoom($room) && !$roomMember->getWriteRight()){
                array_push($message, ['danger', "You do not have permission for this action."]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
            } elseif (count($room->getNotes()) >= $room->getUser()->getPlan()->getMaxRoomsNotes()) {
                if ($room->getUser() == $this->getUser()) {
                    array_push($message, ['danger', "Your room can only contains ".$room->getUser()->getPlan()->getMaxRoomsNotes()." notes"]);
                    array_push($message, ['info', 'Upgrade your plan to create more notes <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.']);
                }
                else {
                    array_push($message, ['danger', "This room can only contains ".$room->getUser()->getPlan()->getMaxRoomsNotes()." notes"]);
                    array_push($message, ['info', 'If you are admin please upgrade your plan to create more notes <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.']);
                }
            } else {
                $roomNotes = new RoomNotes();
                $roomNotes->setUser($this->getUser());
                $roomNotes->setRoom($room);
                $em->persist($roomNotes);
                $em->flush();
                $status = true;
                $note = [
                    'id' => $roomNotes->getId(),
                    'title' => $roomNotes->getTitle(),
                    'autor' => $this->fullName($roomNotes->getUser()),
                    'edited' => $roomNotes->getEdited()->format('H:i:s d-m-Y'),
                    'content' => $roomNotes->getContent(),
                ];
            }
            $arrData = ['status' => $status,'messages' => $message, 'note' => $note ?? null];
            return new JsonResponse($arrData);
        } else {
            return $this->redirectToRoute('panel_room');
        }
    }

    private function fullName(User $user)
    {
        $out = "";
        if(!empty($user->getFirstName())){
            $out .= $user->getFirstName();
            if(!empty($user->getLastName()))
                $out .= " ".$user->getLastName();
        } elseif (!empty($user->getLastName()))
            $out .= $user->getLastName();
        else
            $out .= $user->getUserName();
        return $out;
    }

    /**
     * @Route("/room/{url}/{roomNotes}/update", name="room_note_update")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     * @ParamConverter("roomNotes", class="AppBundle:RoomNotes")
     */
    public function updateNoteAction(Request $request, Room $room = null, RoomNotes $roomNotes = null)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $message = [];
            $status = false;
            $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
            if(!$this->isMyNote($roomNotes) && !$this->isMyRoom($room)){
                array_push($message, ['danger', "This is not your note"]);
            } elseif ($roomMember !== null && $roomMember->getWriteRight() == false) {
                array_push($message, ['danger', "You do not have permission for this action"]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
            } else {
                if ($request->request->get('title')) {
                    $now = new \DateTime("now");
                    $roomNotes->setTitle($request->request->get('title'));
                    $roomNotes->setEdited($now);
                    $em->flush();
                }
                $status = true;
            }
            $arrData = ['status' => $status,'messages' => $message, 'edited' => $now->format('H:i:s d-m-Y')];
            return new JsonResponse($arrData);
        } else {
            return $this->redirectToRoute('panel_room');
        }
    }
    
    /**
     * @Route("/room/{url}/{roomNotes}/content", name="room_note_update_content")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     * @ParamConverter("roomNotes", class="AppBundle:RoomNotes")
     */
    public function updateNoteContentAction(Request $request, Room $room = null, RoomNotes $roomNotes = null)
    {
        if ($request->isXmlHttpRequest() && $roomNotes !== null) {
            $em = $this->getDoctrine()->getManager();
            $message = [];
            $status = false;
            $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
            if(!$this->isMyNote($roomNotes) && !$this->isMyRoom($room)){
                array_push($message, ['danger', "This is not your note"]);
            } elseif ($roomMember !== null && $roomMember->getWriteRight() == false) {
                array_push($message, ['danger', "You do not have permission for this action"]);
            } elseif ($room->getDisabled()) {
                array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
            } else {
                if ($request->request->get('content')) {
                    $now = new \DateTime("now");
                    $em = $this->getDoctrine()->getManager();
                    $roomNotes->setContent($request->request->get('content'));
                    $roomNotes->setEdited($now);
                    $em->flush();
                }
                $status = true;
            }
            $arrData = ['status' => $status,'messages' => $message, 'edited' => !empty($now) ? $now->format('H:i:s d-m-Y') : null];
            return new JsonResponse($arrData);
        } else {
            return $this->redirectToRoute('panel_room');
        }
    }
    
        /**
         * @Route("/room/{url}/update-room", name="room_update_room")
         * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
         */
        public function updateRoomAction(Request $request, Room $room = null)
        {
            if ($request->isXmlHttpRequest()) {
                $message = [];
                $status = false;
                if (!$this->isMyRoom($room)) {
                    array_push($message, ['danger', "This is not your room"]);
                } elseif ($room->getDisabled()) {
                    array_push($message, ['danger', "Room \"".$room->getName()."\" is disabled"]);
                } else {
                    $em = $this->getDoctrine()->getManager();
                    if($request->request->get('name')){
                        $room->setName($request->request->get('name'));
                    }
                    $em->flush();
                    $status = true;
                }
                $arrData = ['status' => $status,'messages' => $message];
                return new JsonResponse($arrData);
            } else {
                return $this->redirectToRoute('panel_room');
            }
        }
    
        /**
         * @Route("/room/{url}/change-password", name="room_change_password")
         * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
         */
        public function changePassAction(Request $request, Room $room = null)
        {
            $oldP = $request->request->get('old');
            $newP = $request->request->get('new');
            $repeatP = $request->request->get('repeat');
            if ($request->isXmlHttpRequest()){
                $message = [];
                $status = false;
                if (!$this->isMyRoom($room)) {
                    array_push($message, ['danger', "This is not your room."]);
                }elseif ($room->getDisabled()) {
                    array_push($message, ['danger', 'Room \"'.$room->getName().'\" is disabled.']);
                }elseif (!empty($oldP) && !empty($newP) && !empty($repeatP)) {
                    $em = $this->getDoctrine()->getManager();
                    $encoder = $this->get('security.encoder_factory')->getEncoder(Room::class);
                    if($encoder->isPasswordValid($room->getPassword(), $oldP, $room)) {
                        if ($newP == $repeatP) {
                            $encodedPassword = $encoder->encodePassword($newP, $room);
                            $room->setPassword($encodedPassword);
                            $em->flush();
                            array_push($message, ['success', "Password changed."]);
                            $status = true;
                        } else {
                            array_push($message, ['danger', "New passwords not match."]);
                        }
                    } else {
                        array_push($message, ['danger', "Password is not valid."]);
                    }
                } else {
                    array_push($message, ['danger', "Fields are empty."]);
                }
                $arrData = ['status' => $status,'messages' => $message];
                return new JsonResponse($arrData);
            } else {
                return $this->redirectToRoute('panel_room');
            }
        }
    
        /**
         * @Route("/room/{url}/enable-password", name="room_enable_password")
         * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
         */
        public function enablePassAction(Request $request, Room $room = null)
        {
            $newP = $request->request->get('new');
            $repeatP = $request->request->get('repeat');
            if ($request->isXmlHttpRequest()){
                $message = [];
                $status = false;
                if (!$this->isMyRoom($room)) {
                    array_push($message, ['danger', "This is not your room."]);
                }elseif ($room->getDisabled()) {
                    array_push($message, ['danger', 'Room \"'.$room->getName().'\" is disabled.']);
                }elseif ($room->getPassword() !== null) {
                    array_push($message, ['danger', "The password for the room is active."]);
                }elseif (!empty($newP) && !empty($repeatP)) {
                    $em = $this->getDoctrine()->getManager();
                    $encoder = $this->get('security.encoder_factory')->getEncoder(Room::class);
                        if ($newP == $repeatP) {
                            $encodedPassword = $encoder->encodePassword($newP, $room);
                            $room->setPassword($encodedPassword);
                            $em->flush();
                            array_push($message, ['success', "Password enabled."]);
                            $status = true;
                        } else {
                            array_push($message, ['danger', "New passwords not match."]);
                        }
                } else {
                    array_push($message, ['danger', "Fields are empty."]);
                }
                $arrData = ['status' => $status,'messages' => $message];
                return new JsonResponse($arrData);
            } else {
                return $this->redirectToRoute('panel_room');
            }
        }
    
        /**
         * @Route("/room/{url}/disable-password", name="room_disable_password")
         * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
         */
        public function disablePassAction(Request $request, Room $room = null)
        {
            $newP = $request->request->get('pass');
            if ($request->isXmlHttpRequest()){
                $message = [];
                $status = false;
                if (!$this->isMyRoom($room)) {
                    array_push($message, ['danger', "This is not your room."]);
                }elseif ($room->getDisabled()) {
                    array_push($message, ['danger', 'Room \"'.$room->getName().'\" is disabled.']);
                }elseif ($room->getPassword() === null) {
                    array_push($message, ['danger', "The password is disabled."]);
                }elseif (!empty($newP)) {
                    $em = $this->getDoctrine()->getManager();
                    $encoder = $this->get('security.encoder_factory')->getEncoder(Room::class);
                    if($encoder->isPasswordValid($room->getPassword(), $newP, $room)) {
                        $room->setPassword(null);
                        $em->flush();
                        array_push($message, ['success', "Password disabled."]);
                        $status = true;

                    } else {
                        array_push($message, ['danger', "Password is incorrect."]);
                    }
                } else {
                    array_push($message, ['danger', "Fields are empty."]);
                }
                $arrData = ['status' => $status,'messages' => $message];
                return new JsonResponse($arrData);
            } else {
                return $this->redirectToRoute('panel_room');
            }
        }
    
        /**
         * @Route("/room/{url}/enter-password", name="room_enter_password", methods={"POST"})
         * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
         */
        public function enterPassAction(Request $request, Room $room = null)
        {
            $submittedToken = $request->request->get('token');
            if (!$request->isXmlHttpRequest() && $this->isCsrfTokenValid('room-password', $submittedToken)) {
                $em = $this->getDoctrine()->getManager();
                $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
                if (!$this->isMyRoom($room) && !$room->getPublic() && empty($roomMember)) {
                    $this->addFlash(
                        'danger',
                        'This room is not public.'
                    );
                    return $this->redirectToRoute('panel_room');
                } elseif ($room->getDisabled()) {
                            $this->addFlash(
                                'warning',
                                'Room \"'.$room->getName().'\" is disabled.'
                            );
                } else {
                    if($request->request->get('_password')){
                        $pass = $request->request->get('_password');
                        $encoder = $this->get('security.encoder_factory')->getEncoder(Room::class);
                        if($encoder->isPasswordValid($room->getPassword(), $pass, $room)) {
                            $session = $this->get('session');
                            $rooms_pass = $session->get('rooms_pass', []);
                            $rooms_pass[$room->getUrl()] = $pass;
                            $session->set('rooms_pass', $rooms_pass);
                            return $this->redirectToRoute('room_url', ['url' => $room->getUrl()]);
                        } else {
                            return $this->redirectToRoute('room_url', ['url' => $room->getUrl(), 'mess' => 'inv']);
                        }
                    } else {
                        return $this->redirectToRoute('room_url', ['url' => $room->getUrl()]);
                    }
                }
            } else {
                return $this->redirectToRoute('panel_room');
            }
        }
    
    // /**
    //  * @Route("/room/{url}/upload-image", name="room_upload_image")
    //  * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
    //  */
    // public function uploadImageAction(Request $request, Room $room = null)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $img = $em->getRepository(NoteFiles::class)->find(3);
    //     $fileObj = new NoteFiles();
    //     $file = file_get_contents($this->getParameter('note_files_directory')."/".$img->getFileName());
    //     // die();
    //     // this condition is needed because the 'brochure' field is not required
    //     // so the PDF file must be processed only when a file is uploaded
    //     $em = $this->getDoctrine()->getManager();
    //     $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
    //     if (!$this->isMyRoom($room) && !$room->getPublic() && empty($roomMember)) {
    //         return $this->json(['messages' => []]);
    //     } else if ($file) {
    //         $originalFilename = $this->generateRandomString();
    //             // this is needed to safely include the file name as part of the URL
    //             $safeFilename = substr($originalFilename, 0, 5);
    //             $newFilename = $safeFilename.'-'.uniqid();
    //         // Move the file to the directory where brochures are stored
    //         try {
    //             // $file->move(
    //             //     $this->getParameter('note_files_directory'),
    //             //     $newFilename
    //             // );
    //             file_put_contents($this->getParameter('note_files_directory')."/".$newFilename, $file);
    //         } catch (FileException $e) {
    //             // ... handle exception if something happens during file upload
    //         }
    //         // updates the 'brochureFilename' property to store the PDF file name
    //         // instead of its contents
    //         $fileObj->setFileName($newFilename);
    //         $fileObj->setName($safeFilename);
    //         $fileObj->setUser($this->getUser());
    //         $fileObj->setRoom($room);
    //         $fileObj->setDate(new \DateTime("now"));
    //         $em->persist($fileObj);
    //     }
    //     $em->flush();
    //     return $this->json(['messages' => []]);
    // }
    /**
     * @Route("/room/{url}", name="room_url")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     * @Template
     */
    public function indexAction(Request $request, Room $room = null)
    {
        $reqPass = $room->getPassword() !== null;

        $em = $this->getDoctrine()->getManager();
        $fileObj = new NoteFiles();
        $form = $this->createForm(NoteFilesType::class, $fileObj);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form['file']->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            $em = $this->getDoctrine()->getManager();
            $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
            if (!$this->isMyRoom($room) && !$room->getPublic() && empty($roomMember)) {
                $this->addFlash(
                    'danger',
                    'Not your room.'
                );
            }
            else if(!$this->isMyRoom($room) && !$roomMember->getWriteRight()) {
                $this->addFlash(
                    'danger',
                    'You do not have permission for this action.'
                );
            }
            else if (count($room->getFiles()) >= $room->getUser()->getPlan()->getMaxFilePerRoom()) {
                if ($room->getUser() == $this->getUser()) {
                    $this->addFlash(
                        'info',
                        'Upgrade your plan to upload more files and images <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.'
                    );
                    $this->addFlash(
                        'danger',
                        'You have reached the file limit '.$room->getUser()->getPlan()->getMaxFilePerRoom().' per room.'
                    );
                } else {
                    $this->addFlash(
                        'info',
                        'If you are admin please upgrade plan to upload more files and images <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.'
                    );
                    $this->addFlash(
                        'danger',
                        'This room have reached the file limit '.$room->getUser()->getPlan()->getMaxFilePerRoom().' in this room.'
                    );
                }
            }
            else if ($file && $file->getClientSize() >= ($room->getUser()->getPlan()->getMaxFileSize() * 1048576)) {
                if ($room->getUser() == $this->getUser()) {
                    $this->addFlash(
                        'info',
                        'Upgrade your plan to upload bigger file <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.'
                    );
                    $this->addFlash(
                        'danger',
                        'You have reached the file size limit ('.$room->getUser()->getPlan()->getMaxFileSize().' MB) per file.'
                    );
                }
                else {
                    $this->addFlash(
                        'info',
                        'If you are admin please upgrade your plan to upload bigger file <a class="info-link" href="'.$this->generateUrl('panel_upgrade').'">here</a>.'
                    );
                    $this->addFlash(
                        'danger',
                        'This room have reached the file size limit ('.$room->getUser()->getPlan()->getMaxFileSize().' MB) per file.'
                    );
                }
            }
            else if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('note_files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $fileObj->setFileName($newFilename);
                $fileObj->setName($safeFilename);
                $fileObj->setUser($this->getUser());
                $fileObj->setRoom($room);
                $fileObj->setDate(new \DateTime("now"));
                $em->persist($fileObj);
            }
            $em->flush();
            return $this->redirectToRoute('room_url', ['url' => $room->getUrl()]);
        }



        $session = $this->get('session');
        $rooms_pass = $session->get('rooms_pass', []);
        // die(var_dump($rooms_pass));
        if (array_key_exists($room->getUrl(), $rooms_pass)) {
            $pass = $rooms_pass[$room->getUrl()];
            $encoder = $this->get('security.encoder_factory')->getEncoder(Room::class);
            if($encoder->isPasswordValid($room->getPassword(), $pass, $room))
                $reqPass = false;
        }
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if($room === null)
                return $this->redirectToRoute('panel');
            if($room->getDisabled()){
                $this->addFlash(
                    'danger',
                    'Room \"'.$room->getName().'\" is disabled.'
                );
                return $this->redirectToRoute('panel_room');
            }
            if ($this->isMyRoom($room)) {
                if ($reqPass){
                    return $this->render('@App/main/room_password.html.twig', ['room' => $room]);
                } else {
                    return ['room' => $room, 'wPer' => true, 'fileForm' => $form->createView()];
                }
            }
            elseif ($em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]) !== null) {
                $rm = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $room]);
                $rm->setStatus('approve');
                $em->flush();
                if ($reqPass){
                    return $this->render('@App/main/room_password.html.twig', ['room' => $room]);
                } else {
                    return ['room' => $room, 'wPer' => $rm->getWriteRight(), 'fileForm' => $rm->getWriteRight() ? $form->createView() : null];
                }
            } elseif($room->getPublic()) {
                if ($reqPass){
                    return $this->render('@App/main/room_password.html.twig', ['room' => $room]);
                } else {
                    return ['room' => $room, 'wPer' => false];
                }
            }
        } else {
            if($room === null)
                return $this->redirectToRoute('homepage');
            $session = $this->get('session');
    
            if ($session->get('control_url') === null)
                if ($room->getPublic()){
                    if ($reqPass){
                        return $this->render('@App/main/room_password.html.twig', ['room' => $room]);
                    } else {
                        return $this->render('@App/main/pure_room.html.twig', ['room' => $room, 'wPer' => false]);
                    }
                }
                else
                    return $this->redirectToRoute('homepage');
            else if ($session->get('ask_join', false)){
                $session->set('ask_join', false);
                return $this->render('@App/main/join.html.twig');
            } elseif ($room->getPublic()) {
                if ($reqPass){
                    return $this->render('@App/main/room_password.html.twig', ['room' => $room]);
                } else {
                    return ['room' => $room, 'wPer' => false];
                }
            } else return $this->render('@App/main/pure_room.html.twig', ['room' => $room, 'wPer' => true]);
        }



    }

    /**
     * @Route("/file/{fileName}", name="file_serve")
     * @ParamConverter("file", class="AppBundle:NoteFiles", options={"mapping"={"fileName"="fileName"}})
     */
    public function fileServeAction(Request $request, NoteFiles $file = null)
    {
        if ($file === null){
            throw new NotFoundHttpException('File not exist!');
        }
        $em = $this->getDoctrine()->getManager();
        $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $file->getRoom()]);
        if (!$this->isMyRoom($file->getRoom()) && !$file->getRoom()->getPublic() && empty($roomMember)) {
            throw new NotFoundHttpException('File not exist!');
        } else {
            $filePath = $this->getParameter('note_files_directory')."/".$file->getFileName();
            return new BinaryFileResponse($filePath);
        }
    }

    /**
     * @Route("/file/{fileName}/download", name="file_download")
     * @ParamConverter("file", class="AppBundle:NoteFiles", options={"mapping"={"fileName"="fileName"}})
     */
    public function fileDownloadAction(Request $request, NoteFiles $file = null)
    {
        if ($file === null){
            throw new NotFoundHttpException('File not exist!');
        }
        $em = $this->getDoctrine()->getManager();
        $roomMember = $em->getRepository(RoomMember::class)->findOneBy(['user' => $this->getUser(), 'room' => $file->getRoom()]);
        if (!$this->isMyRoom($file->getRoom()) && !$file->getRoom()->getPublic() && empty($roomMember)) {
            throw new NotFoundHttpException('File not exist!');
        } else {
            $filePath = $this->getParameter('note_files_directory')."/".$file->getFileName();
            $response = new BinaryFileResponse($filePath);
            $mimeTypeGuesser = new FileinfoMimeTypeGuesser();

            // Set the mimetype with the guesser or manually
            if($mimeTypeGuesser->isSupported()){
                // Guess the mimetype of the file according to the extension of the file
                $response->headers->set('Content-Type', $mimeTypeGuesser->guess($filePath));
            }else{
                // Set the mimetype of the file manually, in this case for a text file is text/plain
                $response->headers->set('Content-Type', 'text/plain');
            }

            // Set content disposition inline of the file
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $file->getName().".".explode(".", $file->getFileName())[count(explode(".", $file->getFileName())) - 1]
            );

            return $response;
        }
    }

    /**
     * @Route("/room/{url}/clear", name="room_clear")
     * @ParamConverter("room", class="AppBundle:Room", options={"mapping"={"url"="url"}})
     */
    public function clearRoomAction(Request $request, Room $room = null)
    {
        if($room === null)
            return $this->redirectToRoute('homepage');
        $session = $this->get('session');
        if ($session->get('control_url') === $room->getUrl()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($room);
            $em->flush();
        }
        return $this->redirectToRoute('homepage');
    }

    private function isMyRoom(Room $room = null)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) 
            if ($this->getUser() === $room->getUser())
                return true;
        return false;
    }

    private function isMyNote(RoomNotes $notes = null)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) 
            if ($this->getUser() === $notes->getUser())
                return true;
        return false;
    }
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
