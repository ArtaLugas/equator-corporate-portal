<?php

namespace Database\Seeders;

use App\Models\Project;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use LoadsSeedData;

    private const VALID_STATUS = ['planned', 'ongoing', 'completed'];

    public function run(): void
    {
        foreach ($this->loadData('projects') as $row) {
            $status = in_array($row['status'] ?? '', self::VALID_STATUS, true)
                ? $row['status']
                : 'completed';

            Project::updateOrCreate(
                ['slug' => $this->clip($row['slug'])],
                [
                    // Service scope kini many-to-many (pivot project_service) — tidak di-set di sini.
                    'name_en' => $this->clip($row['project_name']),
                    'short_description_en' => null,
                    'description_en' => $this->nullable($row['description'] ?? null),
                    'featured_image' => $this->nullable($row['image'] ?? null),
                    'client_name' => $this->nullable($row['client_name'] ?? null),
                    'location' => $this->nullable($row['location'] ?? null),
                    'country' => $this->nullable($row['country'] ?? null),
                    'start_date' => $this->nullable($row['start_date'] ?? null),
                    'end_date' => $this->nullable($row['end_date'] ?? null),
                    'status' => $status,
                    'is_featured' => false,
                ]
            );
        }
    }
}
