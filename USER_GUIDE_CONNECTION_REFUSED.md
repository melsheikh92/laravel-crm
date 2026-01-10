# User Guide: Support Feature & Fixes

## ✅ Status: Fully Operational
The Support feature is installed, and the application is running correctly in Docker.

**URLs:**
*   Main App: [http://localhost/](http://localhost/)
*   Support Tickets: [http://localhost/admin/support/tickets](http://localhost/admin/support/tickets)
*   SLA Policies: [http://localhost/admin/support/sla/policies](http://localhost/admin/support/sla/policies)

## ⚠️ "Connection Refused" Error
If you are seeing `SQLSTATE[HY000] [2002] Connection refused`, it means you are likely trying to run **`php artisan` commands locally** on your Mac terminal (e.g., `php artisan migrate` or `php artisan serve`).

This happens because your local terminal cannot reach the database inside Docker directly, or your local `.env` settings conflict with your local environment.

**Solution:**
Do **NOT** run commands locally. Instead, run them inside the running Docker container.

### Correct Way to Run Commands
Run any `artisan` or `composer` command by prefixing it with `docker exec provensuccess_app`.

Examples:
```bash
# Don't run this:
php artisan migrate

# Run this instead:
docker exec provensuccess_app php artisan migrate
```

```bash
# Don't run this:
php artisan cache:clear

# Run this instead:
docker exec provensuccess_app php artisan cache:clear
```

## Troubleshooting
If you still face issues:
1.  **Restart Docker**: `docker compose restart app`
2.  **Verify Web Access**: Open `http://localhost/` in your browser. If it loads, the system is working, and any "Connection Refused" error is strictly from your local command line usage.

The Support feature has been fully deployed (Database & Code). You can start using it immediately via the browser.
