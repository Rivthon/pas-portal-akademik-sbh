<?php

use App\Providers\AppServiceProvider;
use Barryvdh\DomPDF\ServiceProvider;
use RealRashid\SweetAlert\SweetAlertServiceProvider;
use Yajra\DataTables\DataTablesServiceProvider;

return [
    AppServiceProvider::class,
    DataTablesServiceProvider::class,
    ServiceProvider::class,
    SweetAlertServiceProvider::class,
];
