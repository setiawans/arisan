<?php

namespace Tests\Unit\Models;

use App\Group;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_group_has_name_link_method()
    {
        $group = factory(Group::class)->create();

        $this->assertEquals(
            link_to_route('groups.show', $group->name, [$group], [
                'title' => trans(
                    'app.show_detail_title',
                    ['name' => $group->name, 'type' => trans('group.group')]
                ),
            ]), $group->nameLink()
        );
    }

    /** @test */
    public function a_group_has_belongs_to_many_members_relation()
    {
        $group = factory(Group::class)->create();
        $member = factory(User::class)->create();

        $group->members()->attach($member->id);

        $this->seeInDatabase('group_members', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);

        $this->assertInstanceOf(Collection::class, $group->members);
        $this->assertInstanceOf(User::class, $group->members->first());
    }

    /** @test */
    public function a_group_has_add_member_method()
    {
        $user = $this->createUser();
        $group = factory(Group::class)->create();

        $group->addMember($user);

        $this->seeInDatabase('group_members', [
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);
    }

    /** @test */
    public function a_group_has_remove_member_method()
    {
        $user = $this->createUser();
        $group = factory(Group::class)->create();

        $group->addMember($user);

        $groupMember = \DB::table('group_members')->where([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ])->first();

        $group->removeMember($groupMember->id);

        $this->dontSeeInDatabase('group_members', [
            'id'       => $groupMember->id,
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);
    }

    /** @test */
    public function a_group_has_belongs_to_creator_relation()
    {
        $group = factory(Group::class)->make();

        $this->assertInstanceOf(User::class, $group->creator);
        $this->assertEquals($group->creator_id, $group->creator->id);
    }
}
