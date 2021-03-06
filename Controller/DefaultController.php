<?php

namespace whm\HypeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use whm\HypeBundle\Entity\Hype;
use whm\HypeBundle\Events\HypeEvent;

class DefaultController extends Controller
{
    /**
     * This function informs the system about an action that was called using the
     * symfony2 event system.
     *
     * @event event.dispatcher
     *
     * @param Hype $hype
     */
    private function fireEvent(Hype $hype)
    {
        $hypeEvent = new HypeEvent($hype);
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch('whmHype.hype', $hypeEvent);
    }

    /**
     * Hype an element.
     *
     * @param string $type
     * @param string $identifier
     *
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function hypeAction($type, $identifier)
    {
        $user = $this->getUser();

        $repo = $this->getDoctrine()->getRepository('whmHypeBundle:Hype');
        $hyped = $repo->findOneBy(array('user' => $user->getId(), 'type' => $type, 'identifier' => $identifier));

        if (!is_null($hyped)) {
            throw new AccessDeniedHttpException('already hyped');
        }

        $em = $this->getDoctrine()->getManager();

        $hype = new Hype();
        $hype->setIdentifier($identifier);
        $hype->setType($type);
        $hype->setCreated(new \DateTime());

        $hype->setUser($user->getId());

        $em->persist($hype);
        $em->flush();

        $this->fireEvent($hype);

        return new JsonResponse(array('status' => 'success'));
    }

    /**
     * Unhype an element.
     *
     * @param string $type
     * @param string $identifier
     *
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function unHypeAction($type, $identifier)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $repo = $this->getDoctrine()->getRepository('whmHypeBundle:Hype');
        $hyped = $repo->findOneBy(array('user' => $user->getId(), 'type' => $type, 'identifier' => $identifier));

        if (is_null($hyped)) {
            throw new AccessDeniedHttpException('not hyped');
        }

        $em->remove($hyped);
        $em->flush();

        return new JsonResponse(array('status' => 'success'));
    }

    /**
     * Check if a list of elements is hyped be the current user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function isLikedAction(Request $request)
    {
        $user = $this->getUser();

        if (is_null($user)) {
            return new JsonResponse(array('status' => 'not loggin in'));
        }

        $matches = array();

        $elements = $this->prepareData($request->get('elements'));
        $types = array_keys($elements);

        $repo = $this->getDoctrine()->getRepository('whmHypeBundle:Hype');

        foreach ($types as $type) {
            $matches[$type] = $repo->matchByType($user, $type, $elements[$type]);
        }

        return new JsonResponse(array('matches' => $matches, 'status' => 'success'));
    }

    /**
     * Return the number of hypes for a list of elements.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getLikeCountAction(Request $request)
    {
        $counts = array();

        $elements = $this->prepareData($request->get('elements'));
        $types = array_keys($elements);

        $repo = $this->getDoctrine()->getRepository('whmHypeBundle:Hype');

        foreach ($types as $type) {
            $counts[$type] = $repo->getCountsByType($type, $elements[$type]);
        }

        return new JsonResponse(array('status' => 'success', 'counts' => $counts));
    }

    /**
     * Convert the request data to php arrays.
     *
     * @param array $rawData
     *
     * @return array
     */
    private function prepareData($rawData)
    {
        $elements = array();

        foreach ($rawData as $rawElement) {
            $elements[$rawElement['type']][] = $rawElement['identifier'];
        }

        return $elements;
    }
}
