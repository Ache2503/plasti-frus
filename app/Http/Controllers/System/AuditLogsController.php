<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Pagination;
use App\Services\AuditService;

class AuditLogsController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = array_intersect_key($_GET, array_flip(['accion', 'entidad', 'usuario', 'fecha_desde', 'fecha_hasta']));
        $data = AuditService::getAll($page, 50, $filters);
        $pagination = new Pagination($data['items'], $data['total'], $data['page'], $data['perPage']);
        $this->view('system/audit_logs/index', [
            'logs' => $data['items'],
            'total' => $data['total'],
            'pagination' => $pagination,
            'actions' => AuditService::getActions(),
            'entities' => AuditService::getEntities(),
            'filters' => $filters,
            'pageTitle' => 'Registro de Auditor&iacute;a',
        ]);
    }
}
