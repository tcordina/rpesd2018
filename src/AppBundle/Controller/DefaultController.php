<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

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
        $users = $em->getRepository('AppBundle:UserAdmin')->getClassement();

        return $this->render('default/ranking.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/cookie/animation/{checked}", name="cookie_animation")
     * @param $checked
     * @return Response
     */
    public function cookieAnimationAction($checked)
    {
        $response = new Response();
        $cookie = new Cookie('ANIMATIONS', $checked, '2592000', '/');
        $response->headers->setCookie($cookie);
        return $response->send();
    }

}
