<?php

namespace Webkul\Portal\Http\Controllers;

use Illuminate\Routing\Controller;
use Webkul\Support\Repositories\KbArticleRepository;

class KbController extends Controller
{
    public function __construct(protected KbArticleRepository $kbArticleRepository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('query');

        $query = $this->kbArticleRepository->scopeQuery(function ($query) use ($search) {
            $query = $query->published()->customerPortal();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            }

            return $query;
        });

        $articles = $query->paginate(10);

        return view('portal::kb.index', compact('articles'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Fetch article but ensure it is published and visible
        // We can use the repository to find, but we should apply scope check or check after fetch
        // Repository 'find' might not respect scopes unless we apply them (if repo supports criteria).
        // Safest is to use model query via repository or check attributes after.

        $article = $this->kbArticleRepository->findOrFail($id);

        if (!$article->isPublished() || !in_array($article->visibility, ['public', 'customer_portal'])) {
            abort(404);
        }

        $article->incrementViews();

        return view('portal::kb.view', compact('article'));
    }

    /**
     * Handle helpful vote.
     */
    public function vote($id)
    {
        $article = $this->kbArticleRepository->findOrFail($id);

        $vote = request('vote');

        if ($vote == 1) {
            $article->markAsHelpful();
        } else {
            $article->markAsNotHelpful();
        }

        return redirect()->back()->with('voted', true);
    }
}
