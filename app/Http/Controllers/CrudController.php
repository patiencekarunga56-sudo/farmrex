<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Generic list / create / show / edit / delete screens.
 * A resource controller only describes its fields() and columns().
 */
abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;
    protected string $route;          // route name prefix, e.g. "plantings"
    protected string $title;          // "Plantings"
    protected string $singular;       // "planting"
    protected string $subtitle = '';
    protected string $hint = '';      // helper text shown above the form
    protected array $search = [];     // columns searched by the search box ("rel.col" allowed)
    protected array $with = [];
    protected string $orderBy = 'id';
    protected string $orderDir = 'desc';
    protected bool $tracksUser = true;       // store user_id on create
    protected bool $ownerRestricted = true;  // farmers can only see and change their own records

    /** name => [label, type, rules, options, default, col, hint, step] */
    abstract protected function fields(): array;

    /** list of [label, value(Closure), type(text|money|number|date|badge)] */
    abstract protected function columns(): array;

    // ---------------------------------------------------------------- helpers
    protected function enum(array $values): array
    {
        return array_combine($values, $values);
    }

    protected function plantingOptions(): array
    {
        return \App\Models\Planting::with(['crop', 'farm'])
            ->orderByDesc('planting_date')->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->label])->all();
    }

    /**
     * The "which planting?" dropdown used by many forms. When the farmer has no
     * plantings yet, the form shows a link to record one instead of an empty box.
     */
    protected function plantingField(string $label, bool $required = false, int $col = 12): array
    {
        return [
            'label' => $label,
            'type' => 'select',
            'rules' => $required ? 'required' : 'nullable',
            'options' => $this->plantingOptions(),
            'col' => $col,
            'empty_hint' => 'You have not recorded any plantings yet.',
            'empty_link' => ['Record a planting first', route('plantings.create')],
        ];
    }

    protected function baseQuery(): Builder
    {
        return ($this->model)::query()->with($this->with);
    }

    protected function viewData(array $extra = []): array
    {
        return array_merge([
            'title' => $this->title,
            'singular' => $this->singular,
            'subtitle' => $this->subtitle,
            'hint' => $this->hint,
            'route' => $this->route,
            'canModify' => fn ($m) => $this->canModify($m),
        ], $extra);
    }

    protected function canModify(Model $m): bool
    {
        $user = auth()->user();

        if (! $this->ownerRestricted || $user->isAdmin()) {
            return true;
        }

        return isset($m->user_id) && (int) $m->user_id === (int) $user->id;
    }

    protected function rules(?Model $item = null): array
    {
        $rules = [];
        foreach ($this->fields() as $name => $f) {
            $r = $f['rules'] ?? 'nullable';
            $r = is_array($r) ? $r : explode('|', $r);
            if (($f['type'] ?? 'text') === 'select' && ! empty($f['options'])) {
                $r[] = Rule::in(array_keys($f['options']));
            }
            $rules[$name] = $r;
        }

        return $rules;
    }

    /** Adjust validated data before saving. */
    protected function prepare(array $data, ?Model $item): array
    {
        return $data;
    }

    /** Called after a record was created or updated. */
    protected function afterSave(Model $item, bool $created): void
    {
        //
    }

    // ---------------------------------------------------------------- actions
    public function index(Request $request)
    {
        $query = $this->baseQuery();

        if (($term = trim((string) $request->query('q'))) !== '') {
            $query->where(function ($w) use ($term) {
                foreach ($this->search as $col) {
                    if (str_contains($col, '.')) {
                        [$rel, $c] = explode('.', $col, 2);
                        $w->orWhereHas($rel, fn ($r) => $r->where($c, 'like', "%{$term}%"));
                    } else {
                        $w->orWhere($col, 'like', "%{$term}%");
                    }
                }
            });
        }

        $columns = $this->columns();
        if ($this->tracksUser && auth()->user()->isAdmin()) {
            // The administrator sees every farmer's records, so show whose each one is.
            $query->with('user');
            $columns[] = ['label' => 'Recorded by', 'value' => fn ($m) => $m->user?->name ?? '—'];
        }

        $query->orderBy($this->orderBy, $this->orderDir);
        if ($this->orderBy !== 'id') {
            $query->orderByDesc('id');
        }

        return view('crud.index', $this->viewData([
            'items' => $query->paginate(15)->withQueryString(),
            'columns' => $columns,
        ]));
    }

    public function create()
    {
        return view('crud.form', $this->viewData([
            'mode' => 'create',
            'item' => new $this->model,
            'fields' => $this->fields(),
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->prepare($request->validate($this->rules()), null);
        if ($this->tracksUser) {
            $data['user_id'] = $request->user()->id;
        }

        $item = ($this->model)::create($data);
        $this->afterSave($item, true);

        return redirect()->route($this->route.'.index')
            ->with('success', ucfirst($this->singular).' saved successfully.');
    }

    public function show($id)
    {
        $item = $this->baseQuery()->findOrFail($id);

        return view('crud.show', $this->viewData([
            'item' => $item,
            'fields' => $this->fields(),
        ]));
    }

    public function edit($id)
    {
        $item = ($this->model)::findOrFail($id);
        abort_unless($this->canModify($item), 403);

        return view('crud.form', $this->viewData([
            'mode' => 'edit',
            'item' => $item,
            'fields' => $this->fields(),
        ]));
    }

    public function update(Request $request, $id)
    {
        $item = ($this->model)::findOrFail($id);
        abort_unless($this->canModify($item), 403);

        $data = $this->prepare($request->validate($this->rules($item)), $item);
        $item->update($data);
        $this->afterSave($item, false);

        return redirect()->route($this->route.'.index')
            ->with('success', ucfirst($this->singular).' updated successfully.');
    }

    public function destroy($id)
    {
        $item = ($this->model)::findOrFail($id);
        abort_unless($this->canModify($item), 403);

        try {
            $item->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'This '.$this->singular.' is linked to other records and cannot be deleted.');
        }

        return redirect()->route($this->route.'.index')
            ->with('success', ucfirst($this->singular).' deleted.');
    }
}
