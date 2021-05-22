<?php
namespace UserBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use AppBundle\Entity\User;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('fullname', [$this, 'fullName']),
        ];
    }

    public function fullName(User $user)
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
}