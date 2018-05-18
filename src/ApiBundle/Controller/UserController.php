<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * User controller.
 *
 * @Route("/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="api_users_index")
     * @method("GET")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $params = [];
        $criteria = [];
        $output = [];
        if(!$request->query->get('key') || $request->query->get('key') != 'Goy_S4' ){
            Throw new AccessDeniedHttpException();
        }
        if($request->query->get('id'))
            $params['id'] = $request->query->get('id');
        if($request->query->get('username'))
            $params['username'] = $request->query->get('username');
        if($request->query->get('elo'))
            $params['elo'] = $request->query->get('elo');
        if($request->query->get('rank'))
            $params['rank'] = $request->query->get('rank');

        if($request->query->get('orderBy')) {
            $order = $request->query->get('orderBy');
            $orderArray = explode('-', $order);
            $orderBy = array((string) $orderArray[0] => (string) $orderArray[1]);
        }else { $orderBy = null; }
        $limit = $request->query->get('limit') ? $request->query->get('limit') : null;
        $offset = $request->query->get('offset') ? $request->query->get('offset') : null;

        foreach($params as $key=>$param){
            $criteria[$key] = $param;
        }
        if(count($criteria) == 0) {
            $criteria = array('enabled' => 1);
        }

        $users = $this->getDoctrine()->getRepository('AppBundle:UserAdmin')->findBy(
            $criteria,
            $orderBy,
            (int) $limit,
            (int) $offset
        );
        foreach($users as $user) {
            $output[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'elo' => $user->getElo(),
                'rank' => $user->getRank(),
                'wins' => $user->getWins(),
                'losses' => $user->getLosses(),
            ];
        }
        return new JsonResponse($output);
    }
}
