<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    // GET - Récupérer tous les articles
    public function index()
    {
        $articles = Article::all();
        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    // GET - Récupérer un article spécifique
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    // POST - Créer un nouvel article
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'author' => 'required|max:100'
        ]);

        $article = Article::create($validated);

        return response()->json([
            'success' => true,
            'data' => $article
        ], 201);
    }

    // PUT - Mettre à jour un article
    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|max:255',
            'content' => 'sometimes',
            'author' => 'sometimes|max:100'
        ]);

        $article->update($validated);

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    // DELETE - Supprimer un article
    public function destroy($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article supprimé avec succès'
        ]);
    }
}