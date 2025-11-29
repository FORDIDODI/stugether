<?php

namespace App\Controllers\API;

use App\Models\UserModel;
use App\Models\ForumModel;
use App\Models\KanbanModel;
use App\Models\ReminderModel;
use App\Models\DiscussionModel;
use App\Models\NoteModel;
use App\Models\MediaModel;
use App\Models\AnggotaForumModel;
use OpenApi\Attributes as OAT;

class CountController extends BaseAPIController
{
  #[OAT\Get(
    path: "/counts",
    tags: ["Counts"],
    summary: "Get counts for all entities",
    security: [["bearerAuth" => []]],
    responses: [
      new OAT\Response(
        response: 200,
        description: "All entity counts",
        content: new OAT\JsonContent(
          properties: [
            new OAT\Property(
              property: "data",
              type: "object",
              properties: [
                new OAT\Property(property: "users", type: "integer", example: 50),
                new OAT\Property(property: "forums", type: "integer", example: 10),
                new OAT\Property(property: "tasks", type: "integer", example: 25),
                new OAT\Property(property: "reminders", type: "integer", example: 8),
                new OAT\Property(property: "discussions", type: "integer", example: 45),
                new OAT\Property(property: "notes", type: "integer", example: 30),
                new OAT\Property(property: "media", type: "integer", example: 15),
                new OAT\Property(property: "members", type: "integer", example: 75),
              ]
            ),
          ]
        )
      ),
      new OAT\Response(response: 401, description: "Unauthorized")
    ]
  )]
  public function index()
  {
    $counts = [
      'users'      => (new UserModel())->countAllResults(),
      'forums'     => (new ForumModel())->countAllResults(),
      'tasks'      => (new KanbanModel())->countAllResults(),
      'reminders'  => (new ReminderModel())->countAllResults(),
      'discussions' => (new DiscussionModel())->countAllResults(),
      'notes'      => (new NoteModel())->countAllResults(),
      'media'      => (new MediaModel())->countAllResults(),
      'members'    => (new AnggotaForumModel())->countAllResults(),
    ];

    return $this->success($counts);
  }

  #[OAT\Get(
    path: "/counts/{entity}",
    tags: ["Counts"],
    summary: "Get count for a specific entity",
    security: [["bearerAuth" => []]],
    parameters: [
      new OAT\Parameter(
        name: "entity",
        in: "path",
        required: true,
        schema: new OAT\Schema(
          type: "string",
          enum: ["users", "forums", "tasks", "reminders", "discussions", "notes", "media", "members"]
        )
      )
    ],
    responses: [
      new OAT\Response(
        response: 200,
        description: "Entity count",
        content: new OAT\JsonContent(
          properties: [
            new OAT\Property(
              property: "data",
              type: "object",
              properties: [
                new OAT\Property(property: "entity", type: "string", example: "users"),
                new OAT\Property(property: "count", type: "integer", example: 50),
              ]
            ),
          ]
        )
      ),
      new OAT\Response(
        response: 400,
        description: "Invalid entity",
        content: new OAT\JsonContent(
          properties: [
            new OAT\Property(
              property: "error",
              type: "object",
              properties: [
                new OAT\Property(property: "code", type: "integer", example: 400),
                new OAT\Property(property: "message", type: "string", example: "Invalid entity. Valid entities: users, forums, tasks, reminders, discussions, notes, media, members"),
              ]
            ),
          ]
        )
      ),
      new OAT\Response(response: 401, description: "Unauthorized")
    ]
  )]
  public function show(string $entity)
  {
    $validEntities = [
      'users'       => UserModel::class,
      'forums'      => ForumModel::class,
      'tasks'       => KanbanModel::class,
      'reminders'   => ReminderModel::class,
      'discussions' => DiscussionModel::class,
      'notes'       => NoteModel::class,
      'media'       => MediaModel::class,
      'members'     => AnggotaForumModel::class,
    ];

    if (! isset($validEntities[$entity])) {
      return $this->fail('Invalid entity. Valid entities: ' . implode(', ', array_keys($validEntities)), 400);
    }

    $model = new $validEntities[$entity]();
    $count = $model->countAllResults();

    return $this->success([
      'entity' => $entity,
      'count'  => $count,
    ]);
  }

  #[OAT\Get(
    path: "/counts/detailed",
    tags: ["Counts"],
    summary: "Get detailed statistics for all entities",
    security: [["bearerAuth" => []]],
    responses: [
      new OAT\Response(
        response: 200,
        description: "Detailed statistics",
        content: new OAT\JsonContent(
          properties: [
            new OAT\Property(
              property: "data",
              type: "object",
              properties: [
                new OAT\Property(
                  property: "summary",
                  type: "object",
                  properties: [
                    new OAT\Property(property: "users", type: "integer", example: 50),
                    new OAT\Property(property: "forums", type: "integer", example: 10),
                    new OAT\Property(property: "tasks", type: "integer", example: 25),
                    new OAT\Property(property: "reminders", type: "integer", example: 8),
                    new OAT\Property(property: "discussions", type: "integer", example: 45),
                    new OAT\Property(property: "notes", type: "integer", example: 30),
                    new OAT\Property(property: "media", type: "integer", example: 15),
                    new OAT\Property(property: "members", type: "integer", example: 75),
                  ]
                ),
                new OAT\Property(
                  property: "tasks_by_status",
                  type: "object",
                  properties: [
                    new OAT\Property(property: "todo", type: "integer", example: 10),
                    new OAT\Property(property: "doing", type: "integer", example: 8),
                    new OAT\Property(property: "done", type: "integer", example: 7),
                  ]
                ),
                new OAT\Property(
                  property: "forums_by_type",
                  type: "object",
                  properties: [
                    new OAT\Property(property: "akademik", type: "integer", example: 5),
                    new OAT\Property(property: "proyek", type: "integer", example: 2),
                    new OAT\Property(property: "komunitas", type: "integer", example: 2),
                    new OAT\Property(property: "lainnya", type: "integer", example: 1),
                  ]
                ),
                new OAT\Property(
                  property: "forums_by_visibility",
                  type: "object",
                  properties: [
                    new OAT\Property(property: "public", type: "integer", example: 7),
                    new OAT\Property(property: "private", type: "integer", example: 3),
                  ]
                ),
              ]
            ),
          ]
        )
      ),
      new OAT\Response(response: 401, description: "Unauthorized")
    ]
  )]
  public function detailed()
  {
    $userModel = new UserModel();
    $forumModel = new ForumModel();
    $kanbanModel = new KanbanModel();
    $reminderModel = new ReminderModel();
    $discussionModel = new DiscussionModel();
    $noteModel = new NoteModel();
    $mediaModel = new MediaModel();
    $anggotaModel = new AnggotaForumModel();

    // Summary counts
    $summary = [
      'users'      => $userModel->countAllResults(),
      'forums'     => $forumModel->countAllResults(),
      'tasks'      => $kanbanModel->countAllResults(),
      'reminders'  => $reminderModel->countAllResults(),
      'discussions' => $discussionModel->countAllResults(),
      'notes'      => $noteModel->countAllResults(),
      'media'      => $mediaModel->countAllResults(),
      'members'    => $anggotaModel->countAllResults(),
    ];

    // Tasks by status
    $tasksByStatus = [
      'todo'  => $kanbanModel->where('status', 'todo')->countAllResults(),
      'doing' => $kanbanModel->where('status', 'doing')->countAllResults(),
      'done'  => $kanbanModel->where('status', 'done')->countAllResults(),
    ];

    // Forums by type (dynamic from database)
    $forumsByType = [];
    $forumTypes = $forumModel->builder()
      ->select('jenis_forum, COUNT(*) as count')
      ->groupBy('jenis_forum')
      ->get()
      ->getResultArray();

    foreach ($forumTypes as $type) {
      $forumsByType[$type['jenis_forum']] = (int) $type['count'];
    }

    // Forums by visibility
    $forumsByVisibility = [
      'public'  => $forumModel->where('is_public', 1)->countAllResults(),
      'private' => $forumModel->where('is_public', 0)->countAllResults(),
    ];

    return $this->success([
      'summary'              => $summary,
      'tasks_by_status'      => $tasksByStatus,
      'forums_by_type'       => $forumsByType,
      'forums_by_visibility' => $forumsByVisibility,
    ]);
  }

  #[OAT\Get(
    path: "/counts/forums",
    tags: ["Counts"],
    summary: "Get detailed statistics for each forum",
    security: [["bearerAuth" => []]],
    responses: [
      new OAT\Response(
        response: 200,
        description: "Forum statistics",
        content: new OAT\JsonContent(
          properties: [
            new OAT\Property(
              property: "data",
              type: "array",
              items: new OAT\Items(
                type: "object",
                properties: [
                  new OAT\Property(property: "forum_id", type: "integer", example: 1),
                  new OAT\Property(property: "nama", type: "string", example: "Forum Akademik"),
                  new OAT\Property(property: "jenis_forum", type: "string", example: "akademik"),
                  new OAT\Property(property: "is_public", type: "integer", example: 1),
                  new OAT\Property(property: "members", type: "integer", example: 15),
                  new OAT\Property(property: "tasks", type: "integer", example: 8),
                  new OAT\Property(property: "discussions", type: "integer", example: 12),
                  new OAT\Property(property: "notes", type: "integer", example: 10),
                  new OAT\Property(property: "media", type: "integer", example: 5),
                ]
              )
            ),
          ]
        )
      ),
      new OAT\Response(response: 401, description: "Unauthorized")
    ]
  )]
  public function forums()
  {
    $forumModel = new ForumModel();
    $forums = $forumModel->findAll();

    $result = [];
    foreach ($forums as $forum) {
      $forumId = $forum->forum_id;

      $result[] = [
        'forum_id'    => $forumId,
        'nama'        => $forum->nama,
        'jenis_forum' => $forum->jenis_forum,
        'is_public'   => $forum->is_public,
        'members'     => (new AnggotaForumModel())->where('forum_id', $forumId)->countAllResults(),
        'tasks'       => (new KanbanModel())->where('forum_id', $forumId)->countAllResults(),
        'discussions' => (new DiscussionModel())->where('forum_id', $forumId)->countAllResults(),
        'notes'       => (new NoteModel())->where('forum_id', $forumId)->countAllResults(),
        'media'       => (new MediaModel())->where('forum_id', $forumId)->countAllResults(),
      ];
    }

    return $this->success($result);
  }
}
