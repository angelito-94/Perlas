<?php

Route::get('/', function () { return view('welcome');});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');

//Administrador
Route::get('administrador','AdministradorController@AdministradorPrincipal')->name('AdministradorPrincipal');
Route::get('BalanceAnual', 'AdministradorController@BalanceAnual')->name('BalanceAnual');
Route::post('excel', 'AdministradorController@excel')->name('excel');
Route::get('/conosinbalance/{codanio}' ,'AdministradorController@getconosinbalance')->name('getconosinbalance');
Route::get('eliminarbalance' ,'AdministradorController@eliminarbalance')->name('eliminarbalance');
Route::POST('actualizarbalance' ,'AdministradorController@actualizarbalance')->name('actualizarbalance');
Route::get('ResumenBalanceAnual', 'AdministradorController@ResumenBalanceAnual')->name('ResumenBalanceAnual');
Route::get('EstadoResultadosAnual', 'AdministradorController@EstadoResultadosAnual')->name('EstadoResultadosAnual');
Route::post('excelEstadoResultados', 'AdministradorController@excelEstadoResultados')->name('excelEstadoResultados');
Route::get('IndicadoresAnual', 'AdministradorController@IndicadoresAnual')->name('IndicadoresAnual');
Route::get('/indicadores/{codanio}' ,'AdministradorController@indicadores')->name('indicadores');
Route::get('InformacionUsuario','AdministradorController@InformacionUsuario')->name('InformacionUsuario');
Route::post('UpdateUsuario','AdministradorController@UpdateUsuario')->name('UpdateUsuario');
//mensual
Route::get('BalanceMensual', 'AdministradorController@BalanceMensual')->name('BalanceMensual');
Route::get('/Meses/{codanio}' ,'AdministradorController@Meses')->name('Meses');
Route::get('/conosinbalancemensual/{codaniomes}' ,'AdministradorController@conosinbalancemensual')->name('conosinbalancemensual');
Route::post('excelmensual', 'AdministradorController@excelmensual')->name('excelmensual');
Route::get('eliminarbalancemensual' ,'AdministradorController@eliminarbalancemensual')->name('eliminarbalancemensual');
Route::POST('actualizarbalancemensual' ,'AdministradorController@actualizarbalancemensual')->name('actualizarbalancemensual');
Route::get('ResumenBalanceMensual', 'AdministradorController@ResumenBalanceMensual')->name('ResumenBalanceMensual');
Route::get('/conosinresumenmensual/{codaniomes}' ,'AdministradorController@conosinresumenmensual')->name('conosinresumenmensual');
Route::get('IndicadoresMensual', 'AdministradorController@IndicadoresMensual')->name('IndicadoresMensual');
Route::get('/indicadoresm/{codaniomes}' ,'AdministradorController@indicadoresm')->name('indicadoresm');
//semestral
Route::get('BalanceSemestral', 'AdministradorController@BalanceSemestral')->name('BalanceSemestral');
Route::get('/Semestre/{codanio}' ,'AdministradorController@Semestre')->name('Semestre');
Route::get('/conosinbalancesemestral/{codaniosemestre}' ,'AdministradorController@conosinbalancesemestral')->name('conosinbalancesemestral');
Route::post('excelsemestral', 'AdministradorController@excelsemestral')->name('excelsemestral');
Route::get('eliminarbalancesemestral' ,'AdministradorController@eliminarbalancesemestral')->name('eliminarbalancesemestral');
Route::POST('actualizarbalancesemestral' ,'AdministradorController@actualizarbalancesemestral')->name('actualizarbalancesemestral');
Route::get('ResumenBalanceSemestral', 'AdministradorController@ResumenBalanceSemestral')->name('ResumenBalanceSemestral');
Route::get('/conosinresumensemestral/{codaniosemestral}' ,'AdministradorController@conosinresumensemestral')->name('conosinresumensemestral');
Route::get('IndicadoresSemestral', 'AdministradorController@IndicadoresSemestral')->name('IndicadoresSemestral');
Route::get('/indicadoress/{codaniosemestre}' ,'AdministradorController@indicadoress')->name('indicadoress');




//superadministradorusuarios
Route::get('suadministrador/{id}','superAdministradorController@superAdministradorPrincipal')->name('superAdministradorPrincipal');
Route::get('suBalanceAnual', 'superAdministradorController@suBalanceAnual')->name('superAdministradorBalanceAnual');
Route::post('suexcel', 'superAdministradorController@suexcel')->name('superAdministradorexcel');
Route::get('/suconosinbalance/{codanio}' ,'superAdministradorController@sugetconosinbalance')->name('superAdministradorgetconosinbalance');
Route::get('sueliminarbalance' ,'superAdministradorController@sueliminarbalance')->name('superAdministradoreliminarbalance');
Route::POST('suactualizarbalance' ,'superAdministradorController@suactualizarbalance')->name('superAdministradoractualizarbalance');
Route::get('suResumenBalanceAnual', 'superAdministradorController@suResumenBalanceAnual')->name('superAdministradorResumenBalanceAnual');
Route::get('suIndicadoresAnual', 'superAdministradorController@suIndicadoresAnual')->name('superAdministradorIndicadoresAnual');
Route::get('/suindicadores/{codanio}' ,'superAdministradorController@suindicadores')->name('superAdministradorindicadores');
Route::get('InformacionAdministrador','superAdministradorController@InformacionAdministrador')->name('InformacionAdministrador');
Route::post('UpdateAdministrador','superAdministradorController@UpdateAdministrador')->name('UpdateAdministrador');
//mensual
Route::get('suBalanceMensual', 'superAdministradorController@suBalanceMensual')->name('suBalanceMensual');
Route::get('/suMeses/{codanio}' ,'superAdministradorController@suMeses')->name('suMeses');
Route::get('/suconosinbalancemensual/{codaniomes}' ,'superAdministradorController@suconosinbalancemensual')->name('suconosinbalancemensual');
Route::post('suexcelmensual', 'superAdministradorController@suexcelmensual')->name('suexcelmensual');
Route::get('sueliminarbalancemensual' ,'superAdministradorController@sueliminarbalancemensual')->name('sueliminarbalancemensual');
Route::POST('suactualizarbalancemensual' ,'superAdministradorController@suactualizarbalancemensual')->name('suactualizarbalancemensual');
Route::get('suResumenBalanceMensual', 'superAdministradorController@suResumenBalanceMensual')->name('suResumenBalanceMensual');
Route::get('/suconosinresumenmensual/{codaniomes}' ,'superAdministradorController@suconosinresumenmensual')->name('suconosinresumenmensual');
Route::get('suIndicadoresMensual', 'superAdministradorController@suIndicadoresMensual')->name('suIndicadoresMensual');
Route::get('/suindicadoresm/{codaniomes}' ,'superAdministradorController@suindicadoresm')->name('suindicadoresm');








Route::get('susadministrador','superAdministradorController@superAdministradoresPrincipal')->name('superAdministradoresPrincipal');
Route::get('susBalanceAnual', 'superAdministradorController@susBalanceAnual')->name('superAdministradoresBalanceAnual');
Route::post('susexcel', 'superAdministradorController@susexcel')->name('superAdministradoresexcel');
Route::get('/susconosinbalance/{codanio}' ,'superAdministradorController@susgetconosinbalance')->name('superAdministradoresgetconosinbalance');
Route::get('suseliminarbalance' ,'superAdministradorController@suseliminarbalance')->name('superAdministradoreseliminarbalance');
Route::POST('susactualizarbalance' ,'superAdministradorController@susactualizarbalance')->name('superAdministradoresactualizarbalance');
Route::get('susResumenBalanceAnual', 'superAdministradorController@susResumenBalanceAnual')->name('superAdministradoresResumenBalanceAnual');
Route::get('susIndicadoresAnual', 'superAdministradorController@susIndicadoresAnual')->name('superAdministradoresIndicadoresAnual');
Route::get('/susindicadores/{codanio}' ,'superAdministradorController@susindicadores')->name('superAdministradoresindicadores');
Route::get('susadministradorreg','superAdministradorController@superAdministradoresRegistrar')->name('superAdministradoresRegistrar');
Route::post('/susadministrador/addUsuario','superAdministradorController@addUsuario')->name('addUsuario');
Route::post('/susadministrador/editUsuario','superAdministradorController@editUsuario')->name('editUsuario');
Route::get('susadministradorano','superAdministradorController@superAdministradoresAno')->name('superAdministradoresAno');
Route::post('/susadministrador/addAno','superAdministradorController@addAno')->name('addAno');
Route::post('/susadministrador/deleteAno','superAdministradorController@deleteAno')->name('deleteAno');
