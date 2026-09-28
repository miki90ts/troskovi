# PWA i QR računi — evidencija implementacije

Poslednje ažuriranje: 2026-09-12

## Odluke

- PWA je instalabilna, ali u prvoj verziji zahteva internet i ne kešira aplikacione ili korisničke podatke.
- Kamera za fotografisanje računa dostupna je samo za garancijske troškove.
- QR verifikacija dostupna je za sve troškove i čuva samo zvanični URL Poreske uprave.
- Fotografija i QR ostaju opcioni.
- OCR i automatski uvoz podataka nisu deo ove verzije.
- Slike računa se i dalje brišu nakon isteka garancije; QR URL ostaje.

## Checklist

- [x] Zabeležene odluke, acceptance kriterijumi i deployment koraci.
- [x] Postojeći lint i format dug doveden na zeleno.
- [x] Dodat manifest, ikone, minimalni service worker i install UX.
- [x] Dodati feature flagovi za PWA instalaciju i QR skener.
- [x] Dodat nullable `receipt_verification_url` kroz bazu, API i TypeScript tipove.
- [x] Dodata mobilna kamera i bezbedna obrada slike za garancije.
- [x] Dodat lokalni QR skener, image fallback i ručni URL unos za sve troškove.
- [x] Dodat link za zvaničnu proveru u formi i tabelama.
- [x] Dodati frontend i backend testovi.
- [x] Svi lint, format, typecheck, frontend testovi, build i Pest testovi prolaze.
- [ ] Produkcioni smoke test završen.

## Početno stanje

- PHP/Pest: 104 testa, 578 provera — prolaze.
- TypeScript: prolazi.
- Produkcioni Vite build: prolazi kada je Herd PHP u `PATH`-u.
- ESLint: 31 postojeća problema.
- Prettier: 93 postojeća neformatirana fajla.
- Produkcija: `https://troskovi.pcn.rs/`, Apache, HTTPS i root scope `/`.
- `manifest.webmanifest` i `sw.js` trenutno nisu objavljeni.

## Acceptance kriterijumi

- Instalaciono dugme je dostupno samo kada ima smisla i nestaje kada je aplikacija instalirana.
- Chrome/Edge/Android koriste sistemski install prompt; iOS dobija jasna Add to Home Screen uputstva.
- Service worker nema `fetch` handler i ne kešira HTML, API, slike računa ili finansijske podatke.
- Kamera se uključuje samo na eksplicitan klik i svi video trackovi se gase pri uspehu, grešci i zatvaranju.
- Backend i frontend prihvataju samo zvanične HTTPS verifikacione URL-ove Poreske uprave.
- Postojeće transakcije i garancije bez fotografije ili QR URL-a nastavljaju da rade bez promena.

## Kako da PWA prvo probaš lokalno na laptopu

### Zašto trenutno nema dugmeta „Instaliraj aplikaciju“

U trenutnom lokalnom `.env` fajlu nisu upisani PWA i QR feature flagovi. Kada flag ne postoji, aplikacija namerno koristi vrednost `false`. Zbog toga se manifest ne dodaje u stranicu, service worker se ne registruje i dugme se ne prikazuje.

Drugi razlog je lokalna adresa `http://troskovi.test`. PWA, service worker i kamera zahtevaju bezbedan HTTPS kontekst. Chrome pravi izuzetak za `http://localhost`, ali taj izuzetak ne važi za običan HTTP domen `troskovi.test`.

Potrebno je rešiti obe stvari: uključiti flag i uključiti HTTPS.

### Korak 1 — uključi HTTPS u Laravel Herd-u

Najlakši način na Windows-u:

1. Otvori Laravel Herd.
2. Otvori deo **Sites**.
3. Pronađi sajt `troskovi` odnosno `troskovi.test`.
4. Uključi opciju HTTPS ili klikni na ikonu otvorenog katanca.
5. Ako Windows traži potvrdu za lokalni sertifikat, dozvoli je.
6. Otvori `https://troskovi.test` i proveri da browser ne prikazuje upozorenje za sertifikat.

Ista stvar može iz PowerShell-a, ako je Herd CLI dostupan:

```powershell
cd C:\Users\Lenovo\Herd\troskovi
herd secure troskovi
```

Zvanično Herd uputstvo: [Securing Sites with TLS](https://herd.laravel.com/docs/windows/sites/securing-sites).

### Korak 2 — promeni lokalni `.env`

U fajlu `C:\Users\Lenovo\Herd\troskovi\.env` pronađi `APP_URL` i promeni ga, pa dodaj dva feature flaga:

```dotenv
APP_URL=https://troskovi.test

FEATURE_PWA_INSTALL=true
FEATURE_RECEIPT_QR_SCAN=true
FISCAL_VERIFICATION_HOSTS=suf.purs.gov.rs
```

Ne menjaj `APP_KEY`, bazu, lozinke ili druge postojeće vrednosti.

### Korak 3 — očisti Laravel config cache

U PowerShell-u, iz foldera projekta, pokreni:

```powershell
& 'C:\Users\Lenovo\.config\herd\bin\php85\php.exe' artisan optimize:clear
```

Ako je komanda uspešna, Laravel će prijaviti da su cache fajlovi obrisani.

### Korak 4 — napravi svež frontend build

Ako je `npm run dev` već pokrenut u drugom terminalu, zaustavi ga sa `Ctrl+C`. Zatim pokreni:

```powershell
$env:Path = 'C:\Users\Lenovo\.config\herd\bin\php85;' + $env:Path
npm run build
```

Sačekaj poruku da je build uspešno završen. Upozorenje o velikom JavaScript chunk-u nije greška i ne sprečava test.

### Korak 5 — proveri statičke PWA fajlove

U Chrome-u ili Edge-u pojedinačno otvori:

- `https://troskovi.test/manifest.webmanifest`
- `https://troskovi.test/sw.js`
- `https://troskovi.test/pwa-192x192.png`
- `https://troskovi.test/pwa-512x512.png`

Svaka adresa mora da se otvori bez 404 greške.

### Korak 6 — proveri instalaciju

1. Koristi običan Chrome ili Edge prozor. Nemoj koristiti Incognito/InPrivate.
2. Otvori `https://troskovi.test`.
3. Prijavi se u aplikaciju.
4. Uradi hard refresh sa `Ctrl+Shift+R`.
5. Sačekaj nekoliko sekundi.
6. Dugme **Instaliraj aplikaciju** treba da se pojavi u gornjem desnom delu headera.
7. Klikni dugme i potvrdi sistemski prozor za instalaciju.
8. Aplikacija treba da se otvori u posebnom prozoru bez standardne browser trake.
9. Posle instalacije dugme treba da nestane — to je namerno ponašanje.

### Ako se dugme i dalje ne pojavljuje

Proveri sledeće redom:

1. Adresa mora počinjati sa `https://`, ne sa `http://`.
2. U `.env` mora tačno pisati `FEATURE_PWA_INSTALL=true`.
3. Ponovo pokreni `artisan optimize:clear`.
4. Otvori Developer Tools sa `F12`.
5. Otvori karticu **Application**.
6. U delu **Manifest** ne sme biti crvenih grešaka.
7. U delu **Service Workers** `/sw.js` treba da ima status `activated` ili `running`.
8. Ako je aplikacija već instalirana, dugme se neće prikazati. Proveri Start meni ili `chrome://apps`.
9. Ako lokalni service worker ima staro stanje, samo za lokalni `troskovi.test` idi na **Application → Storage → Clear site data**, zatim zatvori tab i ponovo otvori sajt.
10. Firefox desktop nema isti PWA install prompt; tamo je dugme namerno sakriveno. Za ovaj test koristi Chrome ili Edge.

### Korak 7 — probaj kameru i QR

1. Otvori formu za novi trošak.
2. Akcija **Skeniraj QR** treba da bude vidljiva i kada trošak nije garancijski.
3. Klikni je i dozvoli pristup kameri.
4. Ako nemaš QR pri ruci, proveri da možeš izabrati sliku ili ručno nalepiti zvanični URL.
5. Zatvori skener i proveri da se kamera ugasila.
6. Označi **Ovo je garancijski račun**.
7. Treba da vidiš odvojene akcije **Fotografiši račun** i **Izaberi sliku**.
8. Na laptopu će fotografisanje koristiti podržanu web-kameru/file-picker mogućnost browsera; na telefonu traži zadnju kameru.

## Deployment na produkciju — detaljno uputstvo za prvi put

Produkcija je `https://troskovi.pcn.rs/`. Sledeće korake radi polako i tačno ovim redosledom. Nemoj preskakati backup i nemoj odmah uključivati feature flagove.

### Važna pravila pre početka

- Ne radi deployment kada ti internet ili struja nisu stabilni.
- Ne zatvaraj tabove sa hosting panelom dok ne završiš proveru.
- Ne briši produkcioni `.env` i ne zamenjuj ga lokalnim `.env` fajlom.
- Ne pokreći `migrate:fresh`, `db:wipe`, `migrate:reset` ili `migrate:rollback`.
- Ne briši postojeću bazu i ne uvozi backup osim ako je stvarno došlo do gubitka podataka.
- Laravel aplikacioni folder i javni web folder nisu nužno isti. Zadrži postojeću produkcionu strukturu koja već radi.
- Ako bilo koji korak prijavi grešku, stani. Nemoj nasumično nastavljati na sledeći korak.

### Faza 0 — zapiši gde se šta nalazi

Pre izmene napravi malu belešku sa sledećim podacima:

- URL: `https://troskovi.pcn.rs/`
- naziv hosting panela, na primer cPanel;
- apsolutna putanja Laravel projekta na serveru;
- putanja javnog foldera koji domen koristi;
- aktivna PHP verzija — mora biti PHP 8.3 ili novija;
- naziv produkcione baze;
- naziv Git grane ili način na koji inače postavljaš fajlove;
- vreme kada si počeo deployment.

U primerima ispod koristi se zamišljena putanja `/home/USERNAME/troskovi`. Na serveru je obavezno zameni stvarnom putanjom. Nemoj slepo kopirati `USERNAME`.

Ako ne znaš putanju:

1. Otvori hosting File Manager.
2. Pronađi postojeći fajl `artisan`.
3. Folder koji sadrži `artisan`, `app`, `bootstrap`, `config` i `vendor` je Laravel aplikacioni folder.
4. Proveri gde se nalaze javni `index.php`, `.htaccess`, `build`, `favicon.svg` i ostali sadržaji iz lokalnog `public` foldera.
5. Ne menjaj document root domena tokom ovog deploymenta.

### Faza 1 — pripremi i proveri kod na laptopu

Iz PowerShell-a u lokalnom projektu pokreni:

```powershell
cd C:\Users\Lenovo\Herd\troskovi
npm ci
npm run lint:check
npm run format:check
npm run types:check
npm run test:unit
```

Zatim PHP provere:

```powershell
& 'C:\Users\Lenovo\.config\herd\bin\php85\php.exe' vendor/bin/pint --parallel --test
& 'C:\Users\Lenovo\.config\herd\bin\php85\php.exe' artisan test --compact
```

Na kraju napravi produkcioni build:

```powershell
$env:Path = 'C:\Users\Lenovo\.config\herd\bin\php85;' + $env:Path
npm run build
```

Ne nastavljaj ako bilo koja komanda završi greškom. Uspešan build mora napraviti `public/build/manifest.json` i asset fajlove u `public/build/assets`.

### Faza 2 — pripremi produkcioni `.env`, ali još ne uključuj funkcije

U postojećem produkcionom `.env` dodaj:

```dotenv
FEATURE_PWA_INSTALL=false
FEATURE_RECEIPT_QR_SCAN=false
FISCAL_VERIFICATION_HOSTS=suf.purs.gov.rs
```

Proveri i da produkcija ima:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://troskovi.pcn.rs
```

Ne kopiraj ostale vrednosti iz ovog primera. Posebno ne menjaj `APP_KEY`, DB podatke, email i druge tajne.

### Faza 3 — napravi backup baze

#### Ako koristiš cPanel/phpMyAdmin

1. Otvori cPanel.
2. Otvori **phpMyAdmin**.
3. Sa leve strane izaberi tačnu produkcionu bazu aplikacije.
4. Klikni **Export**.
5. Izaberi **Quick**.
6. Format treba da bude **SQL**.
7. Klikni **Export/Go**.
8. Sačuvaj `.sql` fajl na laptop.
9. Proveri da fajl nije veličine 0 KB.
10. Preimenuj ga smisleno, na primer `troskovi-before-pwa-2026-09-12.sql`.

#### Ako imaš SSH i `mysqldump`

Prvo pročitaj DB naziv i korisnika iz produkcionog `.env`, ali ne šalji te podatke u chat i ne stavljaj lozinku direktno u komandu. Zatim koristi:

```bash
mkdir -p ~/backups
mysqldump -h 127.0.0.1 -u PRODUKCIONI_DB_KORISNIK -p PRODUKCIONA_BAZA > ~/backups/troskovi-before-pwa-2026-09-12.sql
```

Komanda će posebno tražiti lozinku. Proveri rezultat:

```bash
ls -lh ~/backups/troskovi-before-pwa-2026-09-12.sql
```

Backup mora postojati i ne sme biti 0 bajtova.

### Faza 4 — napravi backup postojećeg koda

Ako koristiš cPanel File Manager:

1. Pronađi postojeći Laravel folder.
2. Izaberi ga i klikni **Compress**.
3. Napravi ZIP sa datumom, na primer `troskovi-code-before-pwa-2026-09-12.zip`.
4. Ako prostor dozvoljava, preuzmi ZIP i na laptop.
5. Posebno sačuvaj kopiju produkcionog `.env` na bezbedno mesto.

Ako koristiš Git/SSH, zapiši trenutno postavljeni commit:

```bash
cd /home/USERNAME/troskovi
git rev-parse HEAD
```

Sačuvaj ispisani hash u belešku. To je verzija na koju se vraćaš ako bude potreban rollback.

### Faza 5 — uključi kratki maintenance režim

Na serveru, iz foldera u kome je `artisan`, pokreni:

```bash
php artisan down
```

Otvori produkciju u novom tabu. Treba da vidiš maintenance stranicu. Ako `php` komanda koristi pogrešnu verziju, izaberi PHP 8.3+ u hosting panelu ili koristi tačnu PHP putanju koju daje hosting.

### Faza 6 — postavi nove fajlove

Moraš postaviti ceo izmenjeni Laravel kod, uključujući sledeće važne nove fajlove:

- `config/features.php`;
- novu migraciju u `database/migrations`;
- `app/Rules/SerbianFiscalReceiptUrl.php`;
- izmenjene request/resource/model/service fajlove;
- sve izmenjene `resources` fajlove;
- `public/manifest.webmanifest`;
- `public/sw.js`;
- PWA PNG ikone;
- izmenjeni `public/.htaccess`;
- kompletan lokalno generisan `public/build`;
- `composer.lock`, `package.json` i `package-lock.json`.

Nemoj postavljati:

- lokalni `.env`;
- `.git` ako server ne deployuje preko Git-a;
- `node_modules`;
- lokalne logove i privremene cache fajlove.

#### Ako server koristi Git

Kod prvo mora biti commitovan i pushovan u odgovarajuću granu. Na serveru koristi samo granu za koju znaš da je već povezana sa produkcijom:

```bash
cd /home/USERNAME/troskovi
git status
git pull --ff-only origin IME_PRODUKCIONE_GRANE
```

Ako `git status` pokaže nepoznate lokalne izmene na serveru, stani i nemoj ih brisati.

#### Ako koristiš cPanel File Manager

1. Napravi ZIP deployment paketa na laptopu.
2. Ne stavljaj lokalni `.env` i `node_modules` u ZIP.
3. Postavi ZIP u postojeći aplikacioni folder.
4. Raspakuj ga preko postojećih fajlova tek pošto su backupi završeni.
5. Sadržaj lokalnog `public` foldera mora završiti u stvarnom document root-u produkcionog domena.
6. Proveri da `manifest.webmanifest`, `sw.js` i PWA ikone stvarno postoje u tom javnom folderu.
7. Proveri da skriveni `public/.htaccess` nije izostavljen. U File Manager podešavanjima uključi **Show Hidden Files**.

Ako nemaš SSH/Terminal i server ne može da pokrene Composer, stani pre zamene produkcije i proveri sa hosting podrškom kako se na tom hostingu postavljaju Laravel `vendor` zavisnosti. Nemoj uploadovati Windows `node_modules` na Linux hosting.

### Faza 7 — instaliraj PHP zavisnosti na serveru

Iz aplikacionog foldera pokreni:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

Ova komanda koristi `composer.lock` i ne treba da menja verzije paketa. Nemoj koristiti `composer update` na produkciji.

Frontend se ne mora graditi na serveru ako si postavio kompletan lokalni `public/build`. Ako ipak gradiš na serveru, prvo proveri da ima Node 22 i koristi `npm ci`, ne `npm install`.

### Faza 8 — proveri dozvole foldera

Laravel mora moći da piše u:

- `storage`;
- `bootstrap/cache`.

Na tipičnom Linux hostingu komande su:

```bash
chmod -R ug+rwX storage bootstrap/cache
```

Nemoj nasumično koristiti `chmod -R 777`. Ako hosting koristi drugačijeg vlasnika procesa, prati njegovo Laravel uputstvo.

### Faza 9 — očisti stare cache fajlove

Pokreni:

```bash
php artisan optimize:clear
```

Zatim proveri okruženje i migracije:

```bash
php artisan about
php artisan migrate:status
```

U `artisan about` proveri da je environment `production`, debug `OFF` i URL produkcioni.

### Faza 10 — pokreni aditivnu migraciju

Pokreni tačno:

```bash
php artisan migrate --force
```

Očekivani rezultat je uspešno izvršena migracija koja dodaje `receipt_verification_url` kolonu. Migracija ne briše postojeće transakcije.

Nikada ne pokreći `migrate:fresh`.

Posle migracije ponovo proveri:

```bash
php artisan migrate:status
```

Nova migracija mora imati status **Ran/Yes**.

### Faza 11 — napravi produkcioni config cache

Dok su oba feature flaga još `false`, pokreni:

```bash
php artisan config:cache
php artisan view:cache
```

Ako jedna komanda prijavi grešku, prvo je reši. Nemoj uključivati funkcije dok cache i aplikacija nisu stabilni.

### Faza 12 — isključi maintenance i proveri staru funkcionalnost

Pokreni:

```bash
php artisan up
```

Otvori `https://troskovi.pcn.rs/` u privatnom browser tabu i proveri:

1. login stranica se učitava;
2. prijava radi;
3. dashboard se učitava;
4. postojeći prihodi i troškovi se prikazuju;
5. forma za novi trošak radi;
6. forma za prihod radi;
7. postojeće garancije se prikazuju;
8. postojeća slika računa može da se pregleda i preuzme;
9. kreiranje, izmena i brisanje jedne bezazlene test transakcije rade;
10. nema HTTP 500 greške.

Dok su flagovi `false`, normalno je da nema PWA install dugmeta i QR skenera.

### Faza 13 — proveri PWA fajlove dok su flagovi isključeni

Direktno otvori:

- `https://troskovi.pcn.rs/manifest.webmanifest`;
- `https://troskovi.pcn.rs/sw.js`;
- `https://troskovi.pcn.rs/pwa-192x192.png`;
- `https://troskovi.pcn.rs/pwa-512x512.png`;
- `https://troskovi.pcn.rs/pwa-maskable-512x512.png`.

Svi moraju vratiti HTTP 200, iako dugme još nije vidljivo.

Ako imaš terminal sa `curl`, proveri headere:

```bash
curl -I https://troskovi.pcn.rs/manifest.webmanifest
curl -I https://troskovi.pcn.rs/sw.js
```

Za manifest očekuj `Content-Type: application/manifest+json`. Za `sw.js` očekuj `Cache-Control` sa `no-cache`/`no-store` i `Service-Worker-Allowed: /`.

Ako dobiješ 404, proveri da li su fajlovi postavljeni u pravi javni document root. Ako su headeri pogrešni, proveri da li je postavljen novi `public/.htaccess` i da li Apache dozvoljava njegove direktive.

### Faza 14 — uključi PWA i QR funkcije

Tek kada su prethodne provere uspešne, u produkcionom `.env` promeni:

```dotenv
FEATURE_PWA_INSTALL=true
FEATURE_RECEIPT_QR_SCAN=true
FISCAL_VERIFICATION_HOSTS=suf.purs.gov.rs
```

Zatim obavezno pokreni:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Bez ovog koraka Laravel može nastaviti da koristi stare `false` vrednosti.

### Faza 15 — produkcioni PWA smoke test

U običnom Chrome ili Edge prozoru:

1. otvori produkciju;
2. uradi hard refresh;
3. prijavi se;
4. proveri da se install dugme pojavljuje ako aplikacija nije već instalirana;
5. klikni install i potvrdi;
6. zatvori instaliranu aplikaciju;
7. ponovo je pokreni iz Start menija;
8. proveri da otvara `/dashboard` u standalone prozoru;
9. proveri login/session ponašanje;
10. proveri da offline režim ne prikazuje stare finansijske podatke — mrežna greška je u ovoj fazi očekivana.

### Faza 16 — produkcioni kamera i QR smoke test

Koristi namensku test transakciju koju posle možeš obrisati:

1. napravi novi trošak bez garancije;
2. proveri da postoji **Skeniraj QR**;
3. odbij dozvolu za kameru i proveri da aplikacija ne puca;
4. ponovo otvori skener i dozvoli kameru;
5. probaj čitljiv fiskalni QR;
6. proveri da strani ili običan QR nije prihvaćen;
7. proveri izbor fotografije QR koda;
8. proveri ručno lepljenje zvaničnog URL-a;
9. sačuvaj trošak i proveri link u tabeli troškova;
10. označi drugi trošak kao garancijski;
11. fotografiši račun;
12. sačuvaj i proveri preview/download u garancijama;
13. proveri QR link i u tabeli garancija;
14. proveri isto na telefonu preko HTTPS-a;
15. obriši test transakcije kada završiš.

Ne koristi pravi finansijski podatak koji ne želiš da menjaš tokom smoke testa.

### Faza 17 — proveri logove

Na serveru proveri Laravel log:

```bash
tail -n 100 storage/logs/laravel.log
```

Ako nemaš SSH, koristi File Manager ili hosting Log Viewer. Obrati pažnju na nove greške u vreme deploymenta, posebno HTTP 500, SQL/migration, permission i image/GD greške.

### Faza 18 — upiši rezultat

U odeljak „Ručni i produkcioni smoke test“ ovog dokumenta upiši:

- datum i vreme;
- ko je radio deployment;
- postavljeni commit/verziju;
- rezultat migracije;
- uređaje i browsere koji su provereni;
- da li manifest i service-worker headeri prolaze;
- eventualne probleme i kako su rešeni.

## Rollback — ako nešto pođe loše

### Najbrži rollback samo novih funkcija

Ako stara aplikacija radi, ali PWA, kamera ili QR prave problem, prvo samo isključi oba flag-a:

```dotenv
FEATURE_PWA_INSTALL=false
FEATURE_RECEIPT_QR_SCAN=false
```

Zatim:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Ovo uklanja install dugme i QR skener bez brisanja podataka. Minimalni service worker nema cache ni `fetch` handler, pa neće nastaviti da služi staru aplikaciju.

### Potpuni rollback koda

Ako postoji šira regresija:

1. Pokreni `php artisan down`.
2. Ostavi oba feature flaga na `false`.
3. Vrati prethodni backup koda ili prethodni Git commit koji si zapisao.
4. Vrati prethodni `public/build`.
5. Pokreni `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction` za vraćeni `composer.lock`.
6. Pokreni `php artisan optimize:clear`.
7. Pokreni `php artisan config:cache` i `php artisan view:cache`.
8. Pokreni `php artisan up`.
9. Ponovo proveri login, dashboard, troškove i garancije.

Ne vraćaj novu migraciju i ne briši `receipt_verification_url` kolonu. Kolona je nullable, stari kod je ignoriše i postojeći podaci ostaju bezbedni. Backup baze čuvaj za pravi gubitak ili oštećenje podataka, ne za običnu frontend grešku.

## Izvršene provere

Rezultat 2026-09-12:

- [x] `npm run lint:check`
- [x] `npm run format:check`
- [x] `npm run types:check`
- [x] `npm run test:unit` — 25 testova u 6 fajlova.
- [x] `npm run build` — produkcioni Vite build uspešan; ZXing ostaje zaseban dinamički chunk.
- [x] Laravel Pint check — prolazi.
- [x] PHP `-l` — 181 PHP fajl bez sintaksnih grešaka.
- [x] `php artisan test --compact` — 112 testova, 628 provera.
- [x] PWA ikone proverene kao PNG u dimenzijama 180×180, 192×192 i 512×512.
- [x] Potvrđeno testom da service worker nema `fetch` handler niti Cache API pozive.

## Ručni i produkcioni smoke test

Status: nije izvršen jer ova radna sesija ne postavlja kod na produkciju. Oba feature flaga ostaju podrazumevano isključena.

- [ ] Android Chrome: instalacija, standalone otvaranje, fotografija i live QR.
- [ ] iPhone Safari: Add to Home Screen uputstvo, instalirana aplikacija, fotografija i QR/foto fallback.
- [ ] Windows Chrome: instalacija i QR preko web-kamere/slike.
- [ ] Windows Edge: instalacija i QR preko web-kamere/slike.
- [ ] Firefox: postojeća web aplikacija radi bez install dugmeta i bez regresije.
- [ ] Odbijena dozvola, uređaj bez kamere, nečitljiv/strani QR, offline stanje i istek sesije.
- [ ] Produkcija: manifest MIME, service-worker cache header i `Service-Worker-Allowed: /`.
- [ ] Rezultat i datum produkcionog smoke testa upisani u ovaj dokument.

## Odloženo

- OCR srpskih računa.
- Automatsko prepoznavanje ukupnog iznosa, prodavnice, datuma i stavki.
- Strukturisana tabela stavki i analitika po artiklima.
- Direktna integracija sa Poreskom upravom ako postane dostupan odgovarajući javni API.
- Offline unos, background sync, push obaveštenja i objavljivanje u prodavnicama aplikacija.
