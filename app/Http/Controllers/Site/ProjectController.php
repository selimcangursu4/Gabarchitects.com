<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProjectProgress;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /** /projeler ve /projeler/kategori/{category} */
    public function index(?ProjectCategory $category = null): View
    {
        abort_if($category && ! $category->is_active, 404);

        $base = fn () => Project::published()->with(['cover', 'category'])
            ->when($category, fn ($q) => $q->where('project_category_id', $category->id));

        return view('pages.projects.index', [
            'category' => $category,
            'categories' => ProjectCategory::active()->whereHas('projects', fn ($q) => $q->published())->get(),
            // Talep 3: devam eden projeler ayrı bölüm (yoksa bölüm gizlenir)
            'ongoing' => $base()->progress(ProjectProgress::Ongoing)->ordered()->get(),
            'projects' => $base()->where(fn ($q) => $q->whereNull('progress')->orWhere('progress', '!=', ProjectProgress::Ongoing->value))
                ->ordered()->paginate(12),
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->isPubliclyVisible(), 404);

        $project->load(['cover', 'category', 'gallery', 'services' => fn ($q) => $q->published()]);

        return view('pages.projects.show', [
            'project' => $project,
            'previous' => $project->previous(),
            'next' => $project->next(),
            'related' => Project::published()->whereKeyNot($project->id)
                ->when($project->project_category_id, fn ($q) => $q->where('project_category_id', $project->project_category_id))
                ->ordered()->with(['cover', 'category'])->limit(3)->get(),
        ]);
    }
}
