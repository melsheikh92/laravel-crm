<?php

namespace Webkul\Portal\Http\Controllers;

use Illuminate\Routing\Controller;
use Webkul\Support\Repositories\SupportTicketRepository;
use Webkul\Support\Repositories\KbArticleRepository;

class DashboardController extends Controller
{
    public function __construct(
        protected SupportTicketRepository $supportTicketRepository,
        protected KbArticleRepository $kbArticleRepository
    ) {
    }

    public function index()
    {
        $personId = auth()->guard('portal')->user()->person_id;

        // Fetch open tickets count
        $openTicketsCount = $this->supportTicketRepository
            ->where('customer_id', $personId)
            ->whereIn('status', ['open', 'in_progress', 'waiting_customer', 'waiting_internal'])
            ->count();

        // Fetch recently viewed articles (popular articles as no per-user tracking yet)
        $recentArticles = $this->kbArticleRepository->scopeQuery(function ($query) {
            return $query->published()->customerPortal()->popular(3);
        })->paginate(3);

        return view('portal::dashboard.index', compact('openTicketsCount', 'recentArticles'));
    }
}
