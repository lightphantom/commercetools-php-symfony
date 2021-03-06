<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Symfony\CtpBundle\Security\User;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface CtpUser extends UserInterface, EquatableInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getCartId();

    /**
     * @param mixed $cartId
     */
    public function setCartId($cartId);

    /**
     * @return mixed
     */
    public function getCartItemCount();

    /**
     * @param mixed $cartItemCount
     */
    public function setCartItemCount($cartItemCount);

    /**
     * @param $username
     * @param $password
     * @param array $roles
     * @param $id
     * @param $cartId
     * @param $cartItemCount
     * @return CtpUser
     */
    public static function create($username, $password, array $roles, $id, $cartId, $cartItemCount);
}
