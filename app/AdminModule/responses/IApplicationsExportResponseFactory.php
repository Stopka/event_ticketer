<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 23:32
 */

namespace App\AdminModule\Responses;


interface IApplicationsExportResponseFactory {

    public function create(): ApplicationsExportResponse;

}