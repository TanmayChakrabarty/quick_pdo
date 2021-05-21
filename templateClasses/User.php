<?php


namespace templateClasses;


class User
{
    private int $pk_user_id;
    private string $user_name;
    private string $user_gender;

    /**
     * @return int
     */
    public function getPkUserId(): int
    {
        return $this->pk_user_id;
    }

    /**
     * @param int $pk_user_id
     */
    public function setPkUserId(int $pk_user_id): User
    {
        $this->pk_user_id = $pk_user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->user_name;
    }

    /**
     * @param string $user_name
     */
    public function setUserName(string $user_name): User
    {
        $this->user_name = $user_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserGender(): string
    {
        return $this->user_gender;
    }

    /**
     * @param string $user_gender
     */
    public function setUserGender(string $user_gender): User
    {
        $this->user_gender = $user_gender;
        return $this;
    }


}