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

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Kodify\RestaurantApiBundle\Entity\User;

class RestaurantController extends FOSRestController
{

    /**
     * @Route("/restaurant/", name="get_restaurants")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getRestaurantAction(Request $request)
    {
        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');

        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository('KodifyRestaurantApiBundle:Restaurant')->findAll();

        $userToken = null;
        $user = null;
        if ($request->get('userToken') != '') { 
            $user = $em->getRepository('KodifyRestaurantApiBundle:User')->findOneByToken($request->get('userToken'));
        }
                
        foreach ($restaurants as $rest) {
            if ($user instanceof User && $rest->getUser() == $user) {
                $rest->setCurrentUserOwner(true);
            }

            $rest->setUser(null);
        }
        

        $view->setFormat('json');
        $view->setData($restaurants);

        return $handler->handle($view);
    }

    private function getUserFromToken()
    {
        $user = null;
        $userToken = $this->get('request')->get('userToken');
        if (!empty($userToken)) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('KodifyRestaurantApiBundle:User')->findOneByToken($userToken);
        }
        
        return $user;
    }

    /**
     * @Route("/restaurant/{id}/", name="update_location_restaurant")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateRestaurantLocationAction($id, Request $request)
    {
        $restaurantId = $id;
        $user = null;

        $user = $this->getUserFromToken();
        if (!$user instanceof User) {
            throw new AccessDeniedException(); 
        }

        $em = $this->getDoctrine()->getManager();
        $restaurant = $em->getRepository('KodifyRestaurantApiBundle:Restaurant')->findOneById($restaurantId);

        if (!$restaurant instanceof Restaurant) {
            throw new NotFoundHttpException(); 
        } else if ($restaurant->getUser() != $user) {
            throw new AccessDeniedException(); 
        } 

        $restaurant->setLat($request->get('lat'));
        $restaurant->setLon($request->get('lon'));
        $em->persist($restaurant);
        $em->flush();

        $response = array();

        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');
        $view->setFormat('json');
        $view->setData($response);

        return $handler->handle($view);
    }

    /**
     * @Route("/restaurant/{id}/remove/", name="remove_restaurant")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeRestaurantAction($id, Request $request)
    {
        $restaurantId = $id;
        $user = null;

        $user = $this->getUserFromToken();
        if (!$user instanceof User) {
            throw new AccessDeniedException(); 
        }

        $em = $this->getDoctrine()->getManager();
        $restaurant = $em->getRepository('KodifyRestaurantApiBundle:Restaurant')->findOneById($restaurantId);

        if (!$restaurant instanceof Restaurant) {
            throw new NotFoundHttpException(); 
        } else if ($restaurant->getUser() != $user) {
            throw new AccessDeniedException(); 
        } 

        $em->remove($restaurant);
        $em->flush();
        $response = array('markerId' => $restaurantId);
        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');
        $view->setFormat('json');
        $view->setData($response);

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

        $user = $em->getRepository('KodifyRestaurantApiBundle:User')->findOneByToken($request->get('userToken'));
        if (!$user instanceof User) {
            $user = $em->getRepository('KodifyRestaurantApiBundle:User')->findOneByUsername('anon');
        }

        $rest = new Restaurant();
        $rest->setName($name);
        $rest->setAddress($address);
        $rest->setLat($lat);
        $rest->setLon($lon);
        $rest->setDescription($description);
        $rest->setWebsite($website);
        $rest->setUser($user);
        $rest->setCurrentUserOwner(true);

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
