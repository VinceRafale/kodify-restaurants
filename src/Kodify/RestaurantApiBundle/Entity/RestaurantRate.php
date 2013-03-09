<?php

namespace Kodify\RestaurantApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Kodify\RestaurantApiBundle\Repository\RestaurantRateRepository")
 * @ORM\Table(name="RestaurantRate")
 */
class RestaurantRate
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")     
     */
    protected $food;

    /**
     * @ORM\Column(type="integer")     
     */
    protected $service;

    /**
     * @ORM\Column(type="integer")     
     */
    protected $speed;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set food
     *
     * @param integer $food
     * @return RestaurantRate
     */
    public function setFood($food)
    {
        $this->food = $food;
    
        return $this;
    }

    /**
     * Get food
     *
     * @return integer 
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set service
     *
     * @param integer $service
     * @return RestaurantRate
     */
    public function setService($service)
    {
        $this->service = $service;
    
        return $this;
    }

    /**
     * Get service
     *
     * @return integer 
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set speed
     *
     * @param integer $speed
     * @return RestaurantRate
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;
    
        return $this;
    }

    /**
     * Get speed
     *
     * @return integer 
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set restaurant
     *
     * @param \Kodify\RestaurantApiBundle\Entity\Restaurant $restaurant
     * @return RestaurantRate
     */
    public function setRestaurant(\Kodify\RestaurantApiBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;
    
        return $this;
    }

    /**
     * Get restaurant
     *
     * @return \Kodify\RestaurantApiBundle\Entity\Restaurant 
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * Set user
     *
     * @param \Kodify\RestaurantApiBundle\Entity\User $user
     * @return RestaurantRate
     */
    public function setUser(\Kodify\RestaurantApiBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Kodify\RestaurantApiBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}