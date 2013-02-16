<?php

namespace Kodify\RestaurantApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Kodify\RestaurantApiBundle\Repository\RestaurantRepository")
 * @ORM\Table(name="Restaurant", indexes={@ORM\Index(name="lat", columns={"lat", "lon"})})
 */
class Restaurant
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank(message="This field is required")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $description = '';

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $website = '';

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $lat = '';

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $lon = '';

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    protected $address = '';

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $price = 0;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $googlePlacesId = 0;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     */
    private $tags;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set name
     *
     * @param string $name
     * @return Restaurant
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Restaurant
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lat
     *
     * @param string $lat
     * @return Restaurant
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    
        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lon
     *
     * @param string $lon
     * @return Restaurant
     */
    public function setLon($lon)
    {
        $this->lon = $lon;
    
        return $this;
    }

    /**
     * Get lon
     *
     * @return string 
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Restaurant
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Add tags
     *
     * @param \Kodify\RestaurantApiBundle\Entity\Tag $tags
     * @return Restaurant
     */
    public function addTag(\Kodify\RestaurantApiBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;
    
        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Kodify\RestaurantApiBundle\Entity\Tag $tags
     */
    public function removeTag(\Kodify\RestaurantApiBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getTagsArray()
    {
        $response = array();
        foreach ($this->tags as $tag) {
            $response[] = $tag->getName();
        }

        return $response;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Restaurant
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Restaurant
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set googlePlacesId
     *
     * @param string $googlePlacesId
     * @return Restaurant
     */
    public function setGooglePlacesId($googlePlacesId)
    {
        $this->googlePlacesId = $googlePlacesId;
    
        return $this;
    }

    /**
     * Get googlePlacesId
     *
     * @return string 
     */
    public function getGooglePlacesId()
    {
        return $this->googlePlacesId;
    }
}