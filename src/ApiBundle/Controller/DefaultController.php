<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('@Api/Default/index.html.twig');
    }

    /**
     * @Route("/genkey", name="api_genkey")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateKeyAction(Request $request)
    {
        $user = $this->getUser();
        $currentKey = $user->getApiKey();
        $key = $currentKey ? $currentKey : '';
        if ($request->isMethod('post')) {
            $key = $this->genRandString();
            $user->setApiKey($key);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new JsonResponse($key);
        }
        return $this->render('@Api/Default/genkey.html.twig', ['key' => $key]);
    }

    /**
     * @param int $length
     * @return string
     */
    private function genRandString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_!';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if($this->getDoctrine()->getRepository('AppBundle:UserAdmin')->findOneByApiKey($randomString))
            $this->genRandString();
        return $randomString;
    }
}
