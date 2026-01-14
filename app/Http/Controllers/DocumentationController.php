<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Repositories\DocArticleRepository;

class DocumentationController extends Controller
{
    public function __construct(protected DocArticleRepository $docArticleRepository)
    {
    }

    /**
     * Display a listing of the documentation articles.
     */
    public function index()
    {
        $search = request('query');

        $query = $this->docArticleRepository->scopeQuery(function ($query) use ($search) {
            $query = $query->published()->public();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%')
                        ->orWhere('excerpt', 'like', '%' . $search . '%');
                });
            }

            return $query;
        });

        $articles = $query->paginate(10);

        return view('docs.index', compact('articles'));
    }

    /**
     * Display the specified documentation article.
     */
    public function show($id)
    {
        // Fetch article but ensure it is published and public
        // We can use the repository to find, but we should apply scope check or check after fetch
        // Repository 'find' might not respect scopes unless we apply them (if repo supports criteria).
        // Safest is to use model query via repository or check attributes after.

        $article = $this->docArticleRepository->findOrFail($id);

        if (!$article->isPublished() || $article->visibility !== 'public') {
            abort(404);
        }

        $article->incrementViews();

        return view('docs.show', compact('article'));
    }

    /**
     * Handle helpful vote.
     */
    public function vote($id)
    {
        $article = $this->docArticleRepository->findOrFail($id);

        $vote = request('vote');

        if ($vote == 1) {
            $article->markAsHelpful();
        } else {
            $article->markAsNotHelpful();
        }

        return redirect()->back()->with('voted', true);
    }
}
