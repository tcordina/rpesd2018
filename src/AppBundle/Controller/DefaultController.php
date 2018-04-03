<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if($this->getUser()){
            return $this->redirectToRoute('fos_user_profile_show');
        }else{
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * @Route("/classement", name="classement")
     */
    public function rankingAction()
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('fos_user_security_login');
        }
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:UserAdmin')->findBy(
            array(),
            array('elo' => 'desc'),
            50,
            0
        );

        return $this->render('default/ranking.html.twig', [
            'users' => $users
        ]);
    }

}
