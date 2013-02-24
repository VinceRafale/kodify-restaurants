<?php

namespace Kodify\RestaurantApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Kodify\RestaurantApiBundle\Entity\User;
use Kodify\RestaurantApiBundle\Form\UserTypeAdd;

class UserController extends FOSRestController
{
    /**
     * @Route("/user/", name="register")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $form = $this->createForm(new UserTypeAdd(), $user);
        $form->bind($request);

        if (!$form->isValid()) {
            $errrors = $this->getErrorMessages($form);
            $response = array(
                'errors' => $errrors
            );
        } else {
            $userToken = $user->generateToken();

            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));

            $em->persist($user);
            $em->flush();

            $response = array(
                'msg' => 'Thank you!',
                'token' => $userToken
            );
        }

        $view->setFormat('json');
        $view->setData($response);

        return $handler->handle($view);
    }

    /**
     * @Route("/login/", name="login")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function doLoginAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $factory = $this->get('security.encoder_factory');
        $user = $em->getRepository('KodifyRestaurantApiBundle:User')->findOneByUsername($request->get('user'));
        
        if ($user instanceof User) {

            $encoder = $factory->getEncoder($user);
            $encodedPassword = $encoder->encodePassword($request->get('pass'), $user->getSalt());

            if ($encodedPassword === $user->getPassword()) {
                $user->generateToken();
                
                $em->persist($user);
                $em->flush();

                $response = array(
                    'token' => $user->getToken(),
                    'msg' => 'Hello ' . $user->getUsername() . '!!!'
                );
            } else {
                $user = null;
            }
        } 

        if (!$user instanceof User) {
            $response = array('error' => 'Invalid username or password');
        }


        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');
        $view->setFormat('json');
        $view->setData($response);

        return $handler->handle($view);
    }

    /**
     * @Route("/user/token/", name="validate_token")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validateTokenAction(Request $request)
    {
        $user = $this->getUserFromToken();
        
        if ($user instanceof User) {
            $response = array('OK');            
        } else  {
            $response = array('KO');
        }


        $view = View::create();
        $handler = $this->get('fos_rest.view_handler');
        $view->setFormat('json');
        $view->setData($response);

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

    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();
        
        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach($parameters as $var => $value){
                $template = str_replace($var, $value, $template);
            }

            $errors[] = $template;
        }

        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors = array_merge($errors, $this->getErrorMessages($child));
                }
            }
        }

        return $errors;
    }
}