<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Tests\TestCase;

class CommunityClassProfileTest extends TestCase
{
    public function test_dscons_uses_bim_and_mep_class_labels(): void
    {
        app()->instance('brand', Brand::make(['slug' => 'dscons']));

        $user = new User(['class' => 'offer_architect', 'level' => 10]);

        $this->assertSame('BIM Coordinator', $user->class_label);
        $this->assertSame('layers', $user->class_icon);
        $this->assertSame('Chuyên gia BIM/MEP', (new User(['level' => 100]))->job_stage);
        $this->assertSame('BIM & điều phối', brand()->pillarLabel('offer'));
    }

    public function test_other_communities_keep_their_own_class_vocabulary(): void
    {
        app()->instance('brand', Brand::make(['slug' => 'business']));

        $user = new User(['class' => 'offer_architect', 'level' => 10]);

        $this->assertSame('Offer Architect', $user->class_label);
    }
}
