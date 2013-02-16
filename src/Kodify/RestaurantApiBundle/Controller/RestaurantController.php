<?php

namespace Kodify\RestaurantApiBundle\Controller;

use Kodify\RestaurantApiBundle\Entity\Restaurant;
use Kodify\RestaurantApiBundle\Entity\Tag;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantController extends FOSRestController
{

    /**
     * @Route("/restaurant/", name="get_restaurants")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getRestaurantAction()
    {
        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');

        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository('KodifyRestaurantApiBundle:Restaurant')->findAll();
        $view->setFormat('json');
        $view->setData($restaurants);

        return $handler->handle($view);
    }

    /**
     * @Route("/restaurant/", name="put_restaurants")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putRestaurantAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->get('name');
        $address = $request->get('address');
        $lat = $request->get('lat');
        $lon = $request->get('lon');
        $price = $request->get('price');
        $description = $request->get('description');
        $website = $request->get('website');
        $tags = $request->get('tag');

        $rest = new Restaurant();
        $rest->setName($name);
        $rest->setAddress($address);
        $rest->setLat($lat);
        $rest->setLon($lon);
        $rest->setDescription($description);
        $rest->setWebsite($website);

        if (is_array($tags)) {
            foreach ($tags as $t) {
                $tag = $em->getRepository('KodifyRestaurantApiBundle:Tag')->findOneById($t);
                if (!$tag instanceof Tag) {
                    $tag = new Tag();
                    $tag->setName($t);
                    $em->persist($tag);
                }

                $rest->addTag($tag);
            }      
        }  

        $em = $this->getDoctrine()->getManager();
        $em->persist($rest);
        $em->flush();

        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');
        $view->setFormat('json');
        $view->setData(
            array(
                'text' => 'Restaurant added to database',
                'title' => 'Sucess!!!',
                'restaurant' => $rest
            )
        );

        return $handler->handle($view);
    }
}
