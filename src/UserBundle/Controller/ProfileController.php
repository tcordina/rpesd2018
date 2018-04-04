<?php

namespace UserBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Controller\ProfileController as BaseController;

class ProfileController extends BaseController
{
    /**
     * Edit the user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('@FOSUser/Profile/edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

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
        if($this->getUser()) {
            $user = $this->getUser()->getId();
        }else{
            return $this->redirectToRoute('fos_user_security_login');
        }

        $history = $em->getRepository('AppBundle:Partie')->getHistory($user);
        $enCours = $em->getRepository('AppBundle:Partie')->getCurrentGames($user);

        return $this->render('@FOSUser/Profile/history.html.twig', [
            'partiesHistorique' => $history,
            'partiesEnCours' => $enCours
        ]);
    }

}