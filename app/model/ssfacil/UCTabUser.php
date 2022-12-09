<?php

class UCTabUser extends TSSFacilRecord
{
    const TABLENAME  = 'uctabusers';
    const PRIMARYKEY = 'uciduser';
    const IDPOLICY   = 'max'; // {max, serial}

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('ucusername');
        parent::addattribute('uclogin');
        parent::addattribute('senhahash');
        parent::addattribute('ucemail');
    }

    public static function findUser($username)
    {
        return self::where('uclogin', '=', $username)->first();
    }

    // public function get_hashpassword()
    // {
    //     return UCBase::decrypt($this->ucpassword, 0);
    // }
}