<?php

namespace PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use AppBundle\Form\{RoomType,UserType,UserPictureType};
use AppBundle\Entity\{Words,Room,User};

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Session\Session;
use FOS\UserBundle\Model\UserManagerInterface;

use PanelBundle\Form\ChangePasswordFormType;


/**
* @Route("/panel")
*/

class ProfileController extends Controller
{


    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/profile", name="panel_profile")
     * @Template
     */
    public function profileAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        // EDIT INFORMATION
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST') && !isset($pictureform['picture'])){
            $em->flush();
            $this->addFlash(
                'success',
                'Profile changed successfully.'
            );
            return $this->redirectToRoute('panel_profile');
        }

        // CHANGE PASSWORD
        $changeform = $this->createForm(ChangePasswordFormType::class, $user);
        $changeform->handleRequest($request);
        if ($changeform->isSubmitted() && $changeform->isValid() && !isset($pictureform['picture'])) {
            $this->addFlash(
                'success',
                'Password updated successfully.'
            );
            $this->userManager->updateUser($user);
            return $this->redirectToRoute('panel_profile');
        }


        // UPDATE PICTURE
        $pictureform = $this->createForm(UserPictureType::class, $user);
        $pictureform->handleRequest($request);
        if ($pictureform->isSubmitted() && $pictureform->isValid() && $request->isMethod('POST') && isset($pictureform['picture'])){
            $pictureFile = $pictureform['picture']->getData();
            if ($pictureFile) {
                $fileExtension = $pictureFile->guessExtension();
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$fileExtension;

                try {

                    if($fileExtension === 'png')
                        $image = imagecreatefrompng($pictureFile);
                    else if ($fileExtension === 'jpeg')
                        $image = imagecreatefromjpeg($pictureFile);
                    else if ($fileExtension === 'gif')
                        $image = imagecreatefromgif($pictureFile);

                    $filesize = array(
                        'width' => imagesx($image),
                        'height' => imagesy($image)
                    );
                    $sqsize = min($filesize['width'], $filesize['height']);
                    $crop_opt = [
                        'x' => intdiv($filesize['width'] - $sqsize, 2),
                        'y' => intdiv($filesize['height'] - $sqsize, 2),
                        'width' => $sqsize,
                        'height' => $sqsize
                    ];
                    $croped = imagecrop($image, $crop_opt);
                    if ($croped !== false) {
                        $newsize = min($sqsize, 500);
                        if ($newsize == 500) {
                            $dst = imagecreatetruecolor($newsize, $newsize);
                            imagecopyresampled($dst, $croped, 0, 0, 0, 0, $newsize, $newsize, $sqsize, $sqsize);
                        }
                        imagejpeg($dst, $this->getParameter('profile_pictures_directory')."/".$newFilename, $this->getParameter('profile_quality'));
                        // $pictureFile->move(
                        //     $this->getParameter('profile_pictures_directory'),
                        //     $newFilename
                        // );
                        $user->setPictureFilename($newFilename);
                    }
                } catch (FileException $e) {
                    $this->addFlash(
                        'danger',
                        'Error while updating profile picture.'
                    );
                }
            }

            $em->flush();
            $this->addFlash(
                'success',
                'Profile picture updated successfully.'
            );
            return $this->redirectToRoute('panel_profile');
        }
        

        return ['form' => $form->createView(), 'errors' => $form->getErrors(), 'changeform' => $changeform->createView(), 'pictureform' => $pictureform->createView()];
    }
}
