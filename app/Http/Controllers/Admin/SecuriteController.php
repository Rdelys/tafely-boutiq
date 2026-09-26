<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\OtpRequestLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecuriteController extends Controller
{
    private const PAR_PAGE = 30;

    /**
     * Nombre de demandes de code, pour un même email, dans la fenêtre
     * ci-dessous, à partir duquel le comportement est jugé suspect.
     */
    private const SEUIL_TENTATIVES = 5;
    private const FENETRE_HEURES = 1;

    public function index(Request $request): View
    {
        $adminFiltre = $request->query('admin');
        $actionFiltre = $request->query('action', 'toutes');

        $requete = AdminAuditLog::with(['admin:id,email', 'user:id,nom_boutique,email', 'produit:id,nom'])->latest();

        if ($adminFiltre) {
            $requete->where('admin_id', $adminFiltre);
        }

        if ($actionFiltre !== 'toutes') {
            $requete->where('action', $actionFiltre);
        }

        $logs = $requete->paginate(self::PAR_PAGE)->withQueryString();

        $admins = Admin::orderBy('email')->get(['id', 'email']);
        $actions = AdminAuditLog::query()->select('action')->distinct()->pluck('action');

        // ---- Tentatives OTP suspectes : plusieurs codes demandés pour le
        // même email en peu de temps, côté marchand comme côté admin ----
        $seuil = now()->subHours(self::FENETRE_HEURES);

        $otpSuspects = OtpRequestLog::where('created_at', '>=', $seuil)
            ->selectRaw('email, contexte, COUNT(*) as nombre, MAX(created_at) as dernier')
            ->groupBy('email', 'contexte')
            ->having('nombre', '>=', self::SEUIL_TENTATIVES)
            ->orderByDesc('nombre')
            ->get();

        return view('admin.securite.index', compact(
            'logs', 'admins', 'actions', 'adminFiltre', 'actionFiltre', 'otpSuspects'
        ));
    }
}