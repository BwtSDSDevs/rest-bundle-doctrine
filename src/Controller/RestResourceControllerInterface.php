<?php

namespace Niebvelungen\RestBundleDoctrine\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface RestResourceControllerInterface
{
    public function searchEntityAction(Request $request);

    public function updateEntityAction(Request $request, $id);

    public function getEntityByIdAction(Request $request, $id);

    public function insertEntityAction(Request $request);

    public function deleteEntityAction(Request $request, $id);
}
