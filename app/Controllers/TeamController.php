<?php

namespace App\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Controllers\MainController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

class TeamController extends MainController
{
    protected array $middleware = ['auth'];

    private Team $team;

    public function __construct()
    {
        $this->team = new Team();
    }

    // ── GET /teams ────────────────────────────────────────────────────────────

    /**
     * List teams based on role:
     *   admin/manager → all teams
     *   user          → only their own teams
     */
    public function index(): Response
    {
        $userId = ($_SESSION['user_id'] ?? 0);
        $role   = $_SESSION['role'] ?? '';

        $teams = Gate::hasAnyRole(['admin', 'manager'])
            ? $this->team->allActive()
            : $this->team->forUser($userId);

        return view('teams/Index', [
            'title' => 'Teams',
            'teams' => $teams,
        ]);
    }

    // ── GET /teams/create ─────────────────────────────────────────────────────

    public function create(): Response
    {
        if (! Gate::hasAnyRole(['super_admin', 'admin', 'manager'])) {
            Flash::error('You are not authorized to create teams.');
            return redirect('/teams');
        }

        $users = (new User)->active();

        return view('teams.create', [
            'title'    => 'Create Team',
            'users'    => $users,
            'statuses' => Team::STATUSES,
        ]);
    }

    // ── POST /teams ───────────────────────────────────────────────────────────

    public function store(): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to create teams.');
            return redirect('/teams');
        }

        $userId      = ($_SESSION['user_id'] ?? 0);
        $name        = trim($this->request->input('name', ''));
        $description = trim($this->request->input('description', ''));
        $leadId      = $this->request->input('lead_id', 0);
        $status      = $this->request->input('status', 'active');
        $memberIds   = $this->request->input('member_ids', []);

        // ── Validation ────────────────────────────────────────────────────────
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Team name is required.';
        }

        if (! in_array($status, Team::STATUSES, strict: true)) {
            $errors[] = 'Invalid status.';
        }

        if ($errors) {
            Flash::error(implode(' ', $errors));
            return redirect('/teams/create');
        }

        try {
            $teamId = $this->team->create([
                'name'        => $name,
                'description' => $description,
                'lead_id'     => $leadId ?: null,
                'status'      => $status,
                'created_by'  => $userId,
            ]);

            // Add selected members && ignored due to in operation
            // $members = is_array($memberIds)
            //     ? array_map('intval', $memberIds)
            //     : [];

            foreach ($memberIds as $memberId) {
                $role = ($memberId === $leadId) ? 'lead' : 'member';
                $this->team->addMember($teamId, $userId, $memberId, $role);
            }

            // Auto-add lead if not in the members list
            if ($leadId && !in_array($leadId, $memberIds, strict: true)) {
                $this->team->addMember($teamId, $leadId, $userId, 'lead');
            } else {
                // Update user in team_membership from member to lead.

            }

            Flash::success("Team \"{$name}\" created successfully.");
            return redirect("/teams/{$teamId}");
        } catch (\Throwable $e) {
            Flash::error('Something went wrong. Please try again.' . $e);
            // log_message(. $e->getMessage());
            return redirect('/teams/create');
        }
    }

    // ── GET /teams/{id} ───────────────────────────────────────────────────────

    public function showOLD(string $id): Response
    {
        $team = $this->resolveOrAbort((int) $id);

        if ($team instanceof Response) {
            return $team;
        }

        $members   = $this->team->members((int) $id);
        $tasks     = $this->team->tasks((int) $id);
        $taskStats = $this->team->taskStats((int) $id);
        $missions  = $this->team->missions((int) $id);

        // Available users to add (not already members)
        $memberIds      = array_column($members, 'user_id');
        $availableUsers = Gate::hasAnyRole(['admin', 'manager'])
            ? array_filter(
                (new User)->active(),
                fn($u) => ! in_array($u['user_id'], $memberIds, strict: false)
            )
            : [];

        return view('teams.show', [
            'title'          => $team['name'],
            'team'           => $team,
            'members'        => $members,
            'tasks'          => $tasks,
            'taskStats'      => $taskStats,
            'missions'       => $missions,
            'availableUsers' => array_values($availableUsers),
        ]);
    }

    public function show(string $id): Response
    {
        $team = $this->resolveOrAbort((int) $id);
        $userId =  ($_SESSION['user_id'] ?? 0);
        if ($team instanceof Response) {
            return $team;
        }

        $members   = $this->team->members((int) $id);
        $tasks     = $this->team->tasks((int) $id);
        $taskStats = $this->team->taskStats((int) $id);
        $missions  = $this->team->missions((int) $id);

        $memberIds  = array_column($members, 'user_id');
        $isMember   = in_array($userId, $memberIds, strict: true);
        $isPrivileged = Gate::hasAnyRole(['admin', 'manager']);

        // Only team members, admins, or managers can view
        if (! $isMember && ! $isPrivileged) {
            Flash::error('You are not a member of this team.');
            return redirect('/teams');
            // return abort(403, 'You are not a member of this team.');
        }

        // Only admins/managers can add new members
        $availableUsers = $isPrivileged
            ? array_values(array_filter(
                (new User)->active(),
                fn($u) => ! in_array($u['user_id'], $memberIds, strict: true)
            ))
            : [];

        return view('teams.show', [
            'title'          => $team['name'],
            'team'           => $team,
            'members'        => $members,
            'tasks'          => $tasks,
            'taskStats'      => $taskStats,
            'missions'       => $missions,
            'availableUsers' => $availableUsers,
            'canManage'      => $isPrivileged, // useful in the view to hide/show action buttons
        ]);
    }

    // ── GET /teams/{id}/edit ──────────────────────────────────────────────────

    public function edit(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to edit teams.');
            return redirect('/teams');
        }

        $team = $this->resolveOrAbort((int) $id);

        if ($team instanceof Response) {
            return $team;
        }

        $users     = (new User)->active();
        $memberIds = array_column($this->team->members((int) $id), 'user_id');

        return view('teams.edit', [
            'title'     => 'Edit — ' . $team['name'],
            'team'      => $team,
            'users'     => $users,
            'memberIds' => $memberIds,
            'statuses'  => Team::STATUSES,
        ]);
    }

    // ── POST /teams/{id}/update ───────────────────────────────────────────────

    public function update(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to edit teams.');
            return redirect('/teams');
        }

        $team = $this->resolveOrAbort((int) $id);

        if ($team instanceof Response) {
            return $team;
        }

        $name        = trim($this->request->input('name', ''));
        $description = trim($this->request->input('description', ''));
        $leadId      = (int) $this->request->input('lead_id', 0);
        $status      = $this->request->input('status', $team['status']);

        if (empty($name)) {
            Flash::error('Team name is required.');
            return redirect("/teams/{$id}/edit");
        }

        if (! in_array($status, Team::STATUSES, strict: true)) {
            Flash::error('Invalid status.');
            return redirect("/teams/{$id}/edit");
        }

        try {
            $this->team->update((int) $id, [
                'name'        => $name,
                'description' => $description,
                'lead_id'     => $leadId ?: null,
                'status'      => $status,
            ]);

            Flash::success("Team \"{$name}\" updated successfully.");
            return redirect("/teams/{$id}");
        } catch (\Throwable) {
            Flash::error('Something went wrong while saving.');
            return redirect("/teams/{$id}/edit");
        }
    }

    // ── POST /teams/{id}/delete ───────────────────────────────────────────────

    public function destroy(string $id): Response
    {
        if (! Gate::hasRole('admin')) {
            Flash::error('Only admins can delete teams.');
            return redirect('/teams');
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $team   = $this->team->findActive((int) $id);

        if (! $team) {
            Flash::error('Team not found or already deleted.');
            return redirect('/teams');
        }

        try {
            $this->team->softDelete((int) $id, $userId);
            Flash::success("Team \"{$team['name']}\" was deleted.");
        } catch (\Throwable) {
            Flash::error('Something went wrong while deleting.');
        }

        return redirect('/teams');
    }

    // ── POST /teams/{id}/members ───────────────────────────────────────────────

    /**
     * Add a member to the team.
     */
    public function addMember(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to manage members.');
            return redirect("/teams/{$id}");
        }

        $addedBy = ($_SESSION['user_id'] ?? 0);
        $userId  = $this->request->input('user_id', 0);
        $role    = $this->request->input('role', 'member');

        if (! $userId) {
            Flash::error('Please select a user to add.');
            return redirect("/teams/{$id}");
        }

        if (! in_array($role, ['member', 'lead'], strict: true)) {
            $role = 'member';
        }

        $team = $this->team->findActive((int) $id);

        if (! $team) {
            Flash::error('Team not found.');
            return redirect('/teams');
        }

        try {
            $this->team->addMember((int) $id, $userId, $addedBy, $role);

            $this->notifyUsers(
                userIds: [$userId],
                senderId: $addedBy,
                title: 'You were added to a team',
                body: "You have been added to the team: {$team['name']}",
                url: "/teams/{$id}",
            );

            Flash::success('Member added successfully.');
        } catch (\Throwable $e) {
            Flash::error('Something went wrong while adding the member.');
        }

        return redirect("/teams/{$id}");
    }

    // ── POST /teams/{id}/members/remove ──────────────────────────────────────

    /**
     * Remove a member from the team.
     */
    public function removeMember(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to manage members.');
            return redirect("/teams/{$id}");
        }

        $userId = $this->request->input('user_id', 0);

        if (! $userId) {
            Flash::error('No user specified.');
            return redirect("/teams/{$id}");
        }

        try {
            $this->team->removeMember((int) $id, $userId);
            Flash::success('Member removed from the team.');
        } catch (\Throwable) {
            Flash::error('Something went wrong while removing the member.');
        }

        return redirect("/teams/{$id}");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|Response
     */
    private function resolveOrAbort(int $id): array|Response
    {
        $team   = $this->team->findActive($id);
        $userId = ($_SESSION['user_id'] ?? 0);

        if (! $team) {
            Flash::error('Team not found.');
            return redirect('/teams');
        }

        // Regular users can only view their own teams
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            if (! $this->team->hasMember($id, $userId)) {
                Flash::error('You do not have access to this team.');
                return redirect('/teams');
            }
        }

        return $team;
    }

    /**
     * @param array<int, int> $userIds
     */
    private function notifyUsers(
        array $userIds,
        string $senderId,
        string $title,
        string $body,
        string $url,
    ): void {
        $db  = Connection::getInstance();
        $now = date('Y-m-d H:i:s');

        foreach ($userIds as $uid) {
            $db->execute(
                'INSERT INTO notifications (user_id, sender_id, title, body, url, is_read, created_at)
                 VALUES (?, ?, ?, ?, ?, 0, ?)',
                [$uid, $senderId, $title, $body, $url, $now],
            );
        }
    }
}
