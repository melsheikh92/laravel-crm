<?php

namespace Webkul\Portal\Http\Controllers;

use Illuminate\Routing\Controller;
use Webkul\Quote\Repositories\QuoteRepository;

class QuoteController extends Controller
{
    public function __construct(protected QuoteRepository $quoteRepository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personId = auth()->guard('portal')->user()->person_id;

        $quotes = $this->quoteRepository->where('person_id', $personId)
            ->with('leads') // Eager load leads to check status
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal::quotes.index', compact('quotes'));
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $personId = auth()->guard('portal')->user()->person_id;

        $quote = $this->quoteRepository->findOrFail($id);

        if ($quote->person_id != $personId) {
            abort(403);
        }

        return view('portal::quotes.view', compact('quote'));
    }
}
