<?php

namespace Database\Seeders;

use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $masters = User::query()->where('role', 'master')->orderBy('id')->get();
        $master1 = $masters->get(0);
        $master2 = $masters->get(1);

        $requests = [
            [
                'client_name' => 'Иван Петров',
                'phone' => '+7 (999) 111-22-33',
                'address' => 'ул. Ленина, д. 1, кв. 10',
                'problem_text' => 'Не работает отопление в гостиной.',
                'status' => Request::STATUS_NEW,
                'assigned_to' => null,
            ],
            [
                'client_name' => 'Мария Сидорова',
                'phone' => '+7 (999) 222-33-44',
                'address' => 'пр. Мира, д. 5, кв. 42',
                'problem_text' => 'Протекает кран на кухне.',
                'status' => Request::STATUS_ASSIGNED,
                'assigned_to' => $master1?->id,
            ],
            [
                'client_name' => 'Алексей Козлов',
                'phone' => '+7 (999) 333-44-55',
                'address' => 'ул. Гагарина, д. 12',
                'problem_text' => 'Сломался замок входной двери.',
                'status' => Request::STATUS_IN_PROGRESS,
                'assigned_to' => $master1?->id,
            ],
            [
                'client_name' => 'Елена Новикова',
                'phone' => '+7 (999) 444-55-66',
                'address' => 'ул. Садовая, д. 7, кв. 3',
                'problem_text' => 'Замена батареи в ванной.',
                'status' => Request::STATUS_DONE,
                'assigned_to' => $master2?->id,
            ],
            [
                'client_name' => 'Дмитрий Волков',
                'phone' => '+7 (999) 555-66-77',
                'address' => 'пер. Тихий, д. 2',
                'problem_text' => 'Кондиционер не включается. Отказ от заявки.',
                'status' => Request::STATUS_CANCELED,
                'assigned_to' => null,
            ],
        ];

        foreach ($requests as $data) {
            Request::query()->create($data);
        }
    }
}
