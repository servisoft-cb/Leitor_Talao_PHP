<?php

class SSFacilUser extends SystemUser
{
    public static function validate($login)
    {
        if ($login != 'admin')
        {
            $usuario = UCTabUser::findUserInTransaction('ssfacil', [$login]);
            if (!$usuario)
            throw new Exception(_t('Usuário não encontrado.'));  

            $user = self::newFromLogin($login);
            if (!$user)
            {
                $user = new SystemUser();
                $user->id           = $usuario->uciduser;
                $user->name         = $usuario->ucusername;
                $user->login        = $usuario->uclogin;
                $user->password     = strtolower($usuario->senhahash ?? 'sem senha');
                $user->email        = $usuario->ucemail;
                $user->frontpage_id = 41; // BaixarPedidoForm
                $user->store();

                $group = new SystemGroup(2);
                $user->addSystemUserGroup($group);
            } else {
                $user->password = strtolower($usuario->senhahash ?? 'sem senha');
                $user->store();
            }
        }

        return parent::validate($login);
    }
}