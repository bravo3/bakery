<?php
namespace Bravo3\Bakery\Entity;

use Bravo3\Bakery\Enum\RepositoryType;

class Repository
{
    /**
     * @var RepositoryType
     */
    protected $repository_type;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string
     */
    protected $password = null;

    /**
     * @var string
     */
    protected $private_key = null;

    /**
     * @var string
     */
    protected $tag = null;

    /**
     * @var string
     */
    protected $checkout_path;

    /**
     * Set Password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set PrivateKey
     *
     * @param string $private_key
     * @return $this
     */
    public function setPrivateKey($private_key)
    {
        $this->private_key = $private_key;
        return $this;
    }

    /**
     * Get PrivateKey
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->private_key;
    }

    /**
     * Set RepositoryType
     *
     * @param RepositoryType $repository_type
     * @return $this
     */
    public function setRepositoryType(RepositoryType $repository_type)
    {
        $this->repository_type = $repository_type;
        return $this;
    }

    /**
     * Get RepositoryType
     *
     * @return RepositoryType
     */
    public function getRepositoryType()
    {
        return $this->repository_type;
    }

    /**
     * Set Tag
     *
     * @param string $tag
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Get Tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set Uri
     *
     * @param string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Get Uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set Username
     *
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get Username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set CheckoutPath
     *
     * @param string $checkout_path
     * @return $this
     */
    public function setCheckoutPath($checkout_path)
    {
        $this->checkout_path = $checkout_path;
        return $this;
    }

    /**
     * Get CheckoutPath
     *
     * @return string
     */
    public function getCheckoutPath()
    {
        return $this->checkout_path;
    }

} 