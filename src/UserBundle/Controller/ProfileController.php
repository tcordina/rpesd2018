<?php

namespace UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;

class ProfileController extends BaseController
{
    /**
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function otherAction($username)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:UserAdmin')->findOneBy([
            'username' => $username
        ]);
        return $this->render('@FOSUser/Profile/other.html.twig', array(
            'user' => $user,
        ));
    }
}