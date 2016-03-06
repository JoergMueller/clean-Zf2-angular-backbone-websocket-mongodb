<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="Groups", indexes={
 *     @MongoDB\Index(keys={"status"="asc"}),
 *     @MongoDB\Index(keys={"friends"="asc"}),
 *     @MongoDB\Index(keys={"admins"="asc"}),
 *     @MongoDB\Index(keys={"banned"="asc"}),
 *     @MongoDB\Index(keys={"owner"="asc"}),
 *     @MongoDB\Index(keys={"readable"="asc"}),
 *     @MongoDB\Index(keys={"writable"="asc"}),
 * }))
 */
class GroupsModel
{
    /**
     * @MongoDB\Id
     */
    public $id;

    /**
     * @MongoDB\Field(type="date")
     */
    public $datecreate;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
     */
    public $token;

    /**
     * @MongoDB\Field(type="hash")
     */
    public $owner;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
     */
    public $name;

    /**
     * public, private, invisible
     * @MongoDB\Field(type="string")
     */
    public $status;

    /**
     * @MongoDB\Field(type="string")
     */
    public $writable = 'user';

    /**
     * @MongoDB\Field(type="string")
     */
    public $readable = 'user';

    /** @MongoDB\ReferenceMany(targetDocument="UserModel") */
    public $friends = [];

    /** @MongoDB\ReferenceMany(targetDocument="UserModel") */
    public $admin = [];

    /** @MongoDB\ReferenceMany(targetDocument="UserModel") */
    public $banned = [];

    public function __construct(array $params = [])
    {
        $this->datecreate = new \DateTime();
        $this->token = User::newPassword(16, null, 0);

        foreach ($params as $k => $v) {
            $this->{$k} = $v;
        }

    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datecreate
     *
     * @param date $datecreate
     * @return self
     */
    public function setDatecreate($datecreate)
    {
        $this->datecreate = $datecreate;
        return $this;
    }

    /**
     * Get datecreate
     *
     * @return date $datecreate
     */
    public function getDatecreate()
    {
        return $this->datecreate;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set owner
     *
     * @param hash $owner
     * @return self
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get owner
     *
     * @return hash $owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set writable
     *
     * @param string $writable
     * @return self
     */
    public function setWritable($writable)
    {
        $this->writable = $writable;
        return $this;
    }

    /**
     * Get writable
     *
     * @return string $writable
     */
    public function getWritable()
    {
        return $this->writable;
    }

    /**
     * Set readable
     *
     * @param string $readable
     * @return self
     */
    public function setReadable($readable)
    {
        $this->readable = $readable;
        return $this;
    }

    /**
     * Get readable
     *
     * @return string $readable
     */
    public function getReadable()
    {
        return $this->readable;
    }

    /**
     * Add friend
     *
     * @param Metacope\Mcedit\Model\UserModel $friend
     */
    public function addFriend(\Metacope\Mcedit\Model\UserModel $friend)
    {
        $this->friends[] = $friend;
    }

    /**
     * Remove friend
     *
     * @param Metacope\Mcedit\Model\UserModel $friend
     */
    public function removeFriend(\Metacope\Mcedit\Model\UserModel $friend)
    {
        $this->friends->removeElement($friend);
    }

    /**
     * Get friends
     *
     * @return \Doctrine\Common\Collections\Collection $friends
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * Add admin
     *
     * @param Metacope\Mcedit\Model\UserModel $admin
     */
    public function addAdmin(\Metacope\Mcedit\Model\UserModel $admin)
    {
        $this->admin[] = $admin;
    }

    /**
     * Remove admin
     *
     * @param Metacope\Mcedit\Model\UserModel $admin
     */
    public function removeAdmin(\Metacope\Mcedit\Model\UserModel $admin)
    {
        $this->admin->removeElement($admin);
    }

    /**
     * Get admin
     *
     * @return \Doctrine\Common\Collections\Collection $admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Add banned
     *
     * @param Metacope\Mcedit\Model\UserModel $banned
     */
    public function addBanned(\Metacope\Mcedit\Model\UserModel $banned)
    {
        $this->banned[] = $banned;
    }

    /**
     * Remove banned
     *
     * @param Metacope\Mcedit\Model\UserModel $banned
     */
    public function removeBanned(\Metacope\Mcedit\Model\UserModel $banned)
    {
        $this->banned->removeElement($banned);
    }

    /**
     * Get banned
     *
     * @return \Doctrine\Common\Collections\Collection $banned
     */
    public function getBanned()
    {
        return $this->banned;
    }
}
