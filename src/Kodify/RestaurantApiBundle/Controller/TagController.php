<?php

namespace Kodify\RestaurantApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TagController extends FOSRestController
{

    /**
     * @Route("/tag/", name="get_tags")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTagsAction()
    {
        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');

        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository('KodifyRestaurantApiBundle:Tag')->findAll();
        $view->setFormat('json');
        $view->setData($tags);

        return $handler->handle($view);
    }
}