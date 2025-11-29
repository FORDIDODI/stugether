# Dokumentasi API Endpoint

## Authentication

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/auth/register` | POST | Registrasi user baru |
| `/auth/login` | POST | Login user dan mendapatkan JWT token |
| `/auth/logout` | POST | Logout user (stateless) |
| `/auth/me` | GET | Mendapatkan informasi user yang sedang login |

## Users

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/users/{id}` | GET | Menampilkan detail user berdasarkan ID |
| `/users/{id}` | PUT | Update profil user (hanya user sendiri) |

## Forums

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/forums` | POST | Membuat forum baru |
| `/forums` | GET | Mendapatkan daftar forum |
| `/forums/recommended` | GET | Mendapatkan daftar forum yang direkomendasikan |
| `/forums/{id}` | GET | Menampilkan detail forum berdasarkan ID |
| `/forums/{id}` | PATCH | Update forum (hanya admin forum) |
| `/forums/{id}` | DELETE | Hapus forum (hanya admin forum) |

## Forum Members

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/forums/{id}/join` | POST | Bergabung ke dalam forum |
| `/forums/{id}/leave` | POST | Keluar dari forum (hanya anggota forum) |
| `/forums/{id}/members` | GET | Mendapatkan daftar anggota forum |
| `/forums/{id}/members/{member_id}` | PATCH | Update status anggota forum (hanya admin forum) |

## Tasks (Kanban)

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/forums/{id}/tasks` | POST | Membuat task baru di forum (hanya anggota forum) |
| `/forums/{id}/tasks` | GET | Mendapatkan daftar task di forum |
| `/tasks/{id}` | GET | Menampilkan detail task berdasarkan ID |
| `/tasks/{id}` | PATCH | Update task |
| `/tasks/{id}` | DELETE | Hapus task |
| `/tasks/{id}/attachments` | POST | Upload attachment ke task (hanya anggota forum) |

## Reminders

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/tasks/{id}/reminder` | POST | Membuat reminder untuk task |
| `/reminders` | GET | Mendapatkan daftar reminder user |
| `/reminders/{id}` | DELETE | Hapus reminder |

## Discussions

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/forums/{id}/discussions` | POST | Membuat diskusi baru di forum (hanya anggota forum) |
| `/discussions/{id}/replies` | POST | Membalas diskusi (hanya anggota forum) |
| `/forums/{id}/discussions` | GET | Mendapatkan daftar diskusi di forum |
| `/discussions/{id}` | GET | Menampilkan detail diskusi berdasarkan ID |
| `/discussions/{id}` | PATCH | Update diskusi |
| `/discussions/{id}` | DELETE | Hapus diskusi |

## Notes

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/forums/{id}/notes` | POST | Membuat catatan baru di forum (hanya anggota forum) |
| `/forums/{id}/notes` | GET | Mendapatkan daftar catatan di forum |
| `/notes/{id}` | GET | Menampilkan detail catatan berdasarkan ID |
| `/notes/{id}` | PATCH | Update catatan |
| `/notes/{id}` | DELETE | Hapus catatan |

## Media

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/media` | POST | Upload media/file |
| `/forums/{id}/media` | GET | Mendapatkan daftar media di forum |
| `/media/{id}` | GET | Menampilkan detail media berdasarkan ID |
| `/media/{id}` | DELETE | Hapus media |

## Search

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/search` | GET | Pencarian global (forum, task, discussion, notes) |

## Notifications

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/notifications` | GET | Mendapatkan daftar notifikasi user |

## Counts

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/counts` | GET | Mendapatkan jumlah data untuk semua entitas (users, forums, tasks, reminders, discussions, notes, media, members) |
| `/counts/detailed` | GET | Mendapatkan statistik detail untuk semua entitas (termasuk breakdown by status, type, visibility) |
| `/counts/forums` | GET | Mendapatkan statistik detail untuk setiap forum (jumlah anggota, task, discussions, notes, media per forum) |
| `/counts/{entity}` | GET | Mendapatkan jumlah data untuk entitas tertentu. Entity yang valid: `users`, `forums`, `tasks`, `reminders`, `discussions`, `notes`, `media`, `members` |

### Detail Endpoint Counts

#### GET `/counts`
Mengembalikan jumlah data untuk semua entitas sekaligus.

**Response 200 (Success):**
```json
{
  "data": {
    "users": 50,
    "forums": 10,
    "tasks": 25,
    "reminders": 8,
    "discussions": 45,
    "notes": 30,
    "media": 15,
    "members": 75
  }
}
```

#### GET `/counts/{entity}`
Mengembalikan jumlah data untuk entitas tertentu.

**Parameter:**
- `entity` (path, required): Nama entitas yang ingin dihitung. Nilai yang valid:
  - `users` - Jumlah total user
  - `forums` - Jumlah total forum
  - `tasks` - Jumlah total task/kanban
  - `reminders` - Jumlah total reminder
  - `discussions` - Jumlah total diskusi
  - `notes` - Jumlah total catatan
  - `media` - Jumlah total media/file
  - `members` - Jumlah total anggota forum

**Response 200 (Success):**
```json
{
  "data": {
    "entity": "users",
    "count": 50
  }
}
```

**Response 400 (Bad Request):**
```json
{
  "error": {
    "code": 400,
    "message": "Invalid entity. Valid entities: users, forums, tasks, reminders, discussions, notes, media, members"
  }
}
```

**Contoh Request:**
```bash
# Mendapatkan semua count
curl -X GET "https://api.example.com/counts" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Mendapatkan count untuk entitas tertentu
curl -X GET "https://api.example.com/counts/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

curl -X GET "https://api.example.com/counts/forums" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### GET `/counts/detailed`
Mengembalikan statistik detail untuk semua entitas dengan breakdown berdasarkan status, tipe, dan visibility.

**Response 200 (Success):**
```json
{
  "data": {
    "summary": {
      "users": 50,
      "forums": 10,
      "tasks": 25,
      "reminders": 8,
      "discussions": 45,
      "notes": 30,
      "media": 15,
      "members": 75
    },
    "tasks_by_status": {
      "todo": 10,
      "doing": 8,
      "done": 7
    },
    "forums_by_type": {
      "akademik": 5,
      "proyek": 2,
      "komunitas": 2,
      "lainnya": 1
    },
    "forums_by_visibility": {
      "public": 7,
      "private": 3
    }
  }
}
```

#### GET `/counts/forums`
Mengembalikan statistik detail untuk setiap forum, termasuk jumlah anggota, task, discussions, notes, dan media per forum.

**Response 200 (Success):**
```json
{
  "data": [
    {
      "forum_id": 1,
      "nama": "Forum Akademik",
      "jenis_forum": "akademik",
      "is_public": 1,
      "members": 15,
      "tasks": 8,
      "discussions": 12,
      "notes": 10,
      "media": 5
    },
    {
      "forum_id": 2,
      "nama": "Forum Proyek",
      "jenis_forum": "proyek",
      "is_public": 0,
      "members": 8,
      "tasks": 5,
      "discussions": 6,
      "notes": 4,
      "media": 2
    }
  ]
}
```

**Contoh Request:**
```bash
# Mendapatkan statistik detail
curl -X GET "https://api.example.com/counts/detailed" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Mendapatkan statistik per forum
curl -X GET "https://api.example.com/counts/forums" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Documentation

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/docs` | GET | Dokumentasi API (Swagger) |

---

**Catatan:**
- Semua endpoint kecuali `/auth/register` dan `/auth/login` memerlukan JWT token di header Authorization
- `{id}` dan `{member_id}` adalah parameter path yang harus diganti dengan ID aktual
- Filter tambahan seperti `forumAdmin` dan `forumMember` memerlukan permission khusus

