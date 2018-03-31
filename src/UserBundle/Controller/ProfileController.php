<?php

namespace UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        if(null === $user) {
            throw new NotFoundHttpException('L\'utilisateur '.$username.' n\'éxiste pas.');
        }
        if($user == $this->getUser()){
            return $this->redirectToRoute('fos_user_profile_show');
        }
        return $this->render('@FOSUser/Profile/other.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function historyAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getId();

        $history = $em->getRepository('AppBundle:Partie')->getHistory($user);
        $enCours = $em->getRepository('AppBundle:Partie')->getCurrentGames($user);

        return $this->render('@FOSUser/Profile/history.html.twig', [
            'partiesHistorique' => $history,
            'partiesEnCours' => $enCours
        ]);
    }

}