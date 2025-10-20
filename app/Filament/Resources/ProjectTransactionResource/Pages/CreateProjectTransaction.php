<?php

namespace App\Filament\Resources\ProjectTransactionResource\Pages;

use App\Filament\Resources\ProjectTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Project;

class CreateProjectTransaction extends CreateRecord
{
    protected static string $resource = ProjectTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If project_id is passed as a query parameter, find the project and set the project_key
        if (request()->has('project_id')) {
            $project = Project::find(request('project_id'));
            if ($project) {
                $data['project_key'] = $project->key;
            }
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction()
                ->url(
                    fn() => request()->has('project_id')
                        ? route('filament.admin.resources.projects.view', ['record' => request('project_id')])
                        : $this->getResource()::getUrl('index')
                ),
        ];
    }
}
