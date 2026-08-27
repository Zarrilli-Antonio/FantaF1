# Fanta F1

Il fantacalcio della Formula 1 — crea una lega privata con i tuoi amici, fai un'asta a crediti per comporre la rosa di piloti e team, e sfida gli altri gara dopo gara con punteggi calcolati automaticamente sui risultati reali del Mondiale.

Pensato per essere **self-hosted**: un `docker compose up` e sei pronto, dati e account restano sul tuo server.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)

## Cosa fa

- **Leghe private** con codice invito — crea la tua o unisciti a quella di un amico.
- **Asta a crediti** una tantum: ogni utente ha un budget, fa le sue offerte su piloti e team, vince chi offre di più. Un pilota può andare a una sola persona per lega. Finita l'asta la rosa resta fissa per tutta la stagione, niente mercato.
- **Punteggio automatico**: dopo ogni Gran Premio i punti si calcolano da soli sui risultati ufficiali (posizione, pole, giro più veloce, podio, ritiri), con il dettaglio di cosa ha segnato ogni pilota.
- **Grafici** dell'andamento in classifica gara per gara, e dei tempi sul giro di ogni pilota.
- **Notizie** con il recap testuale di ogni Gran Premio, generato automaticamente, visibili anche senza account.
- **Statistiche gara** complete: classifica, tempi, pit stop, giri, per ogni pilota.
- **Multilingua**: italiano, inglese, tedesco, francese.
- **PWA**: installabile su telefono/desktop, funziona offline per la shell dell'app.
- **Tema scuro** di default.

I dati di calendario, piloti e risultati vengono importati automaticamente dalla [Jolpica F1 API](https://github.com/jolpica/jolpica-f1) (gratuita, erede di Ergast). Il progetto non è affiliato alla Formula 1®, alla FIA, né a Jolpica.

## Requisiti

Solo **Docker** e **Docker Compose** — nessun'altra dipendenza da installare sulla macchina.

## Installazione

```bash
git clone https://github.com/<tuo-utente>/fanta-f1.git
cd fanta-f1
cp .env.example .env
docker compose up -d --build
```

Al primo avvio l'app genera da sola la chiave applicativa e crea le tabelle nel database — non serve nessun comando manuale aggiuntivo. Dopo un minuto (il tempo di build), l'app è pronta su **http://localhost:8080**.

Ultimo passo, da fare una sola volta, per importare il calendario e i piloti della stagione in corso:

```bash
docker compose exec app php artisan f1:sync-season
```

Da qui in poi calendario e risultati si aggiornano da soli in automatico (il servizio `scheduler` li controlla ogni giorno/ora). Vai su `http://localhost:8080`, registrati, e crea la tua prima lega.

### Aggiornare una porta diversa da 8080

Modifica `APP_PORT` nel file `.env` prima di avviare i container (o rifai `docker compose up -d` se già avviati), e aggiorna `APP_URL` di conseguenza.

## Metterlo dietro un tuo reverse proxy (opzionale)

Il `docker-compose.yml` pubblica nginx direttamente sulla porta scelta (`APP_PORT`, default 8080) e funziona così com'è. Se vuoi metterlo dietro Traefik, Caddy o un altro proxy con il tuo dominio/TLS, crea un file `docker-compose.override.yml` (viene caricato automaticamente da Docker Compose insieme al file principale, e non va mai messo su Git) con qualcosa del genere:

```yaml
services:
  nginx:
    networks:
      - my_proxy_network
    labels:
      - traefik.enable=true
      - traefik.http.routers.fantaf1.rule=Host(`fantaf1.iltuodominio.it`)
      - traefik.http.routers.fantaf1.entrypoints=websecure
      - traefik.http.routers.fantaf1.tls=true
      - traefik.http.services.fantaf1.loadbalancer.server.port=80

networks:
  my_proxy_network:
    external: true
```

Adatta i nomi di rete e le label al tuo proxy, poi `docker compose up -d`.

## Comandi utili

```bash
# Aggiornare l'app dopo un git pull
docker compose up -d --build

# Log di un servizio
docker compose logs -f app

# Importare/aggiornare manualmente una stagione specifica
docker compose exec app php artisan f1:sync-season 2027

# Importare/forzare i risultati di una gara specifica (ID dalla tabella races)
docker compose exec app php artisan f1:sync-results 1

# Console Laravel (tinker)
docker compose exec app php artisan tinker
```

## Servizi del docker compose

| Servizio | Cosa fa |
|---|---|
| `app` | applicazione Laravel (PHP-FPM) |
| `nginx` | web server, pubblicato su `APP_PORT` |
| `queue` | elabora i job in background (calcolo punteggi dopo ogni gara) |
| `scheduler` | esegue i controlli periodici (nuovi risultati, chiusura round d'asta) |
| `db` | MySQL 8 |
| `redis` | cache, sessioni e code |
| `phpmyadmin` | *(opzionale)* interfaccia web per il database, su `:8081` — rimuovilo dal file se non ti serve |

Dati del database e asset compilati vivono in volumi Docker con nome, indipendenti dai container: puoi ricreare i container senza perdere nulla.

## Stack tecnico

Laravel 11 · Livewire 3 · Tailwind CSS · MySQL · Redis · Vite + `vite-plugin-pwa` · Jolpica F1 API.

## Licenza

MIT — vedi [LICENSE](LICENSE).
