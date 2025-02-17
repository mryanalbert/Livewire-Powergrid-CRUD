<?php

namespace App\Livewire;

use App\Models\people;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Mary\Traits\Toast;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class PeopleTable extends PowerGridComponent
{
    use Toast;
    use WithExport;

    public string $tableName = 'people-table-jjccy4-table';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function header(): array
    {
        return [
            Button::add('bulk-delete')
                ->slot(__('Bulk delete (<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)'))
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('bulkDelete.' . $this->tableName, [])
        ];
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),

            PowerGrid::exportable(fileName: 'my-export-file')
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSoftDeletes(showMessage: true),
        ];
    }

    public function datasource(): Builder
    {
        return people::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('gender')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Gender', 'gender')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name'),
            Filter::boolean('gender'),
        ];
    }

    #[On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            $this->toast(
                type: 'success',
                title: 'No selected recrod.',
                description: null,                  // optional (text)
                position: 'toast-bottom toast-end',    // optional (daisyUI classes)
                icon: 'o-information-circle',       // Optional (any icon)
                css: 'alert-error',                  // Optional (daisyUI classes)
                timeout: 4000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );
            return;
        }

        $this->js('
            Swal.fire({
                title: "Are you sure?",
                text: "You would not be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.call("bulkDeleteConfirmed");
                }
            });
        ');
    }

    public function bulkDeleteConfirmed()
    {
        if ($this->checkboxValues && count($this->checkboxValues) > 0) {
            people::destroy($this->checkboxValues);
            $this->js('window.pgBulkActions.clearAll()'); // clear the count on the interface.
            $this->toast(
                type: 'success',
                title: 'Successfully deleted!',
                description: null,                  // optional (text)
                position: 'toast-bottom toast-end',    // optional (daisyUI classes)
                icon: 'o-information-circle',       // Optional (any icon)
                css: 'alert-success',                  // Optional (daisyUI classes)
                timeout: 4000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );

            $this->checkboxValues = [];
        }
    }

    public function actions(people $row): array
    {
        return [
            Button::add('view')
                ->slot('View')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->route('person.view', ['id' => $row->id])
                ->attributes(['wire:navigate' => true]),
            Button::add('edit')
                ->slot('Edit: ' . $row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id]),
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
