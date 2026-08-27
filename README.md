# Fanta F1

The Formula 1 fantasy game — create a private league with your friends, run a credit auction to build your roster of drivers and teams, and challenge each other race after race with scores calculated automatically from real Championship results.

Built to be **self-hosted**: one `docker compose up` and you're ready — your data and accounts stay on your own server.

👉 [**See the screenshots**](SCREENSHOTS.md) to get a feel for it.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)

## What it does

- **Private leagues** with an invite code — create your own or join a friend's.
- **One-time credit auction**: every user gets a budget, bids on drivers and teams, highest bid wins. Each driver can only go to one person per league. Once the auction ends, the roster is locked for the whole season — no transfer market.
- **Automatic scoring**: after every Grand Prix, points are calculated from the official results (finishing position, pole, fastest lap, podium, retirements), with a full breakdown of what each driver scored.
- **Charts** of the season standings race by race, and each driver's lap times.
- **News feed** with an automatically generated text recap of every Grand Prix, visible even without an account.
- **Full race statistics**: classification, times, pit stops, laps, for every driver.
- **Predictions**: before each race, every league member can bet on pole position, first retirement (DNF), and fastest pit stop — on any driver or team, not just the ones on their roster. Correct predictions earn league points, and everyone's picks are revealed once the race is over.
- **Driver profiles**: bio, nationality, age, current team, and career stats (wins, podiums, poles, fastest laps, retirements).
- **Multi-language**: English, Italian, German, French.
- **PWA**: installable on phone/desktop, works offline for the app shell.
- **Dark theme** by default.

Calendar, driver and result data is imported automatically from the [Jolpica F1 API](https://github.com/jolpica/jolpica-f1) (free, Ergast's successor). This project is not affiliated with Formula 1®, the FIA, or Jolpica.

## Requirements

Just **Docker** and **Docker Compose** — no other dependency to install on the machine.

## Installation

```bash
git clone https://github.com/<your-username>/fanta-f1.git
cd fanta-f1
cp .env.example .env
docker compose up -d --build
```

On first boot the app generates its own application key and creates the database tables — no extra manual command needed. After a minute (build time), the app is ready at **http://localhost:8080**.

Last step, to be done once, to import the current season's calendar and drivers:

```bash
docker compose exec app php artisan f1:sync-season
```

From here on, the calendar and results update automatically on their own (the `scheduler` service checks daily/hourly). Go to `http://localhost:8080`, sign up, and create your first league.

### Using a port other than 8080

Edit `APP_PORT` in the `.env` file before starting the containers (or run `docker compose up -d` again if already running), and update `APP_URL` accordingly.

## Putting it behind your own reverse proxy (optional)

The `docker-compose.yml` publishes nginx directly on the chosen port (`APP_PORT`, default 8080) and works as-is. If you want to put it behind Traefik, Caddy, or another proxy with your own domain/TLS, create a `docker-compose.override.yml` file (automatically loaded by Docker Compose alongside the main file, and should never be committed to Git) with something like:

```yaml
services:
  nginx:
    networks:
      - my_proxy_network
    labels:
      - traefik.enable=true
      - traefik.http.routers.fantaf1.rule=Host(`fantaf1.yourdomain.com`)
      - traefik.http.routers.fantaf1.entrypoints=websecure
      - traefik.http.routers.fantaf1.tls=true
      - traefik.http.services.fantaf1.loadbalancer.server.port=80

networks:
  my_proxy_network:
    external: true
```

Adjust the network names and labels to match your proxy, then `docker compose up -d`.

## Useful commands

```bash
# Update the app after a git pull
docker compose up -d --build

# Tail a service's logs
docker compose logs -f app

# Manually import/refresh a specific season
docker compose exec app php artisan f1:sync-season 2027

# Import/force the results of a specific race (ID from the races table)
docker compose exec app php artisan f1:sync-results 1

# Laravel console (tinker)
docker compose exec app php artisan tinker
```

## Docker Compose services

| Service | What it does |
|---|---|
| `app` | Laravel application (PHP-FPM) |
| `nginx` | web server, published on `APP_PORT` |
| `queue` | processes background jobs (score calculation after each race) |
| `scheduler` | runs periodic checks (new results, auction round closing) |
| `db` | MySQL 8 |
| `redis` | cache, sessions, and queues |
| `phpmyadmin` | *(optional)* web UI for the database, on `:8081` — remove it from the file if you don't need it |

Database data and compiled assets live in named Docker volumes, independent of the containers: you can recreate the containers without losing anything.

## Tech stack

Laravel 11 · Livewire 3 · Tailwind CSS · MySQL · Redis · Vite + `vite-plugin-pwa` · Jolpica F1 API.

## License

MIT — see [LICENSE](LICENSE).
