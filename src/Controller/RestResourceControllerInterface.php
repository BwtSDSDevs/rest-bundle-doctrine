<?php

namespace SdsDev\RestBundleDoctrine\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Dominik Ahammer <ahammer.dominik@gmail.com>
 */
interface RestResourceControllerInterface
{
    public function searchEntityAction(Request $request);

    public function updateEntityAction(Request $request, $id);

    public function getEntityByIdAction(Request $request, $id);

    public function insertEntityAction(Request $request);

    public function deleteEntityAction(Request $request, $id);
}
