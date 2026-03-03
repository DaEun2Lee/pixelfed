# Pixelfed Docker Deployment & Backup Guide

## 📌 Original Repository

Official Pixelfed repository:

https://github.com/pixelfed/pixelfed

---

# 🚀 Deployment Overview

This project runs Pixelfed using Docker containers including:

* Pixelfed (Laravel + Nginx)
* MySQL Database
* Redis
* Caddy (Reverse proxy, optional HTTP/3 support)

---

# ▶️ Basic Startup

Start all services:

```bash
docker-compose up -d
```

Check running containers:

```bash
docker ps
```

Stop services:

```bash
docker-compose down
```

---

# 🗄 Database Backup (Recommended Method)

Instead of using `mysqldump`, this project backs up the **entire Docker volume** for maximum consistency.

## ✅ Backup MySQL Volume

```bash
docker run --rm \
  -v pixelfed_db_data:/volume \
  -v $(pwd):/backup \
  alpine tar czf /backup/db_volume.tar.gz -C /volume .
```

This will generate:

```
db_volume.tar.gz
```

in the current directory.

---

# ♻️ Database Restore

On a new server:

1. Create the Docker volume:

```bash
docker volume create pixelfed_db_data
```

2. Restore the backup:

```bash
docker run --rm \
  -v pixelfed_db_data:/volume \
  -v $(pwd):/backup \
  alpine sh -c "cd /volume && tar xzf /backup/db_volume.tar.gz"
```

3. Start services:

```bash
docker-compose up -d
```

---

# 📂 Important Files to Preserve

For full migration, keep:

```
docker-compose.yml
.env
storage/
db_volume.tar.gz
```

### Critical Notes

* `.env` must contain the original `APP_KEY`
* `storage/` contains uploaded images
* If `APP_KEY` changes, login sessions and encryption break

---

# 🔐 If Changing Server IP or Domain

Update `.env`:

```
APP_URL=https://your-new-domain
```

Then clear Laravel cache:

```bash
docker exec pixelfed-app php artisan config:clear
docker exec pixelfed-app php artisan cache:clear
```

---

# 📦 Full Migration Checklist

✔ Database volume backup
✔ Storage directory copy
✔ .env preserved
✔ docker-compose.yml preserved
✔ APP_URL updated (if needed)

---

# 🧪 Optional: Verify Database Volume Exists

```bash
docker volume ls
```

You should see:

```
pixelfed_db_data
```

---

# 🏁 Final Notes

This method ensures:

* No password handling in CLI
* No partial dump issues
* Exact binary database consistency
* Fast restore on new infrastructure

Recommended for production migration and deployment replication.

---

End of README
