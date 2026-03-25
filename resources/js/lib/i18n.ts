interface MessageDictionary {
    [key: string]: string | MessageDictionary;
}

type MessageValue = string | MessageDictionary;

const messages = {
    auth: {
        welcome: {
            head: 'Dobrodošla',
            workspace: 'Lični finansijski prostor',
            dashboard: 'Pregled',
            loginFirst: 'Prvo prijava',
            smarterCashFlow: 'Pametniji tok novca',
            heroTitle:
                'Organizuj svaki račun, trošak i ponavljajuće plaćanje u jednom elegantnom toku.',
            heroDescription:
                'Troškovi ti daje jasnu sliku stanja, prenosa, kategorija i mesečnih obrazaca kako bi svakodnevne finansije bile uređene umesto rasute.',
            cards: {
                allInOne: 'Sve na jednom mestu',
                allInOneDescription:
                    'Računi, troškovi, izveštaji i ponavljanja.',
                recurring: 'Ponavljanja',
                recurringDescription: 'Prati fiksne mesečne troškove',
                reports: 'Izveštaji',
                reportsDescription: 'Uoči trendove bez suma',
            },
            clarity: {
                badge: 'Napravljen za preglednost',
                title: 'Manje trenja. Bolje odluke.',
                description:
                    'Počni čistom prijavom, pa upravljaj prihodima, troškovima, bankovnim računima i ponavljajućim transakcijama sa jedne dosledne table.',
            },
            mobile: {
                subtitle: 'Pametan tok novca',
                secure: 'Bezbedna prijava',
            },
            loginTitle: 'Tvoj finansijski pregled počinje ovde.',
            loginDescription:
                'Prijavi se da pregledaš stanja, skorašnje transakcije, ponavljajuća plaćanja i izveštaje na jednom mestu.',
            getStarted: 'Započni',
            registerPrompt:
                'Treba ti novi nalog za upravljanje budžetom, računima i ponavljajućim troškovima?',
            loggedIn:
                'Već si prijavljen. Nastavi direktno na pregled i analizu svojih troškova.',
            openDashboard: 'Otvori pregled',
        },
        login: {
            head: 'Prijava',
            eyebrow: 'Dobrodošao nazad',
            title: 'Prijavi se i preuzmi punu kontrolu nad svojim novcem.',
            description:
                'Prati račune, tok troškova i ponavljajuća plaćanja na jednom preglednom mestu.',
            panelBadge: 'Bezbedan pristup',
            panelTitle:
                'Jasniji dnevni pregled svakog dinara, računa i prenosa.',
            panelDescription:
                'Objedini tok novca, kategorije i ponavljajuće troškove u jednoj tabli koja ubrzava odluke.',
            stats: {
                balances: 'Pregled stanja',
                categories: 'Kategorije i izveštaji',
                capture: 'Brz unos troškova',
            },
            email: 'Email adresa',
            password: 'Lozinka',
            forgotPassword: 'Zaboravljena lozinka?',
            rememberMe: 'Zapamti me',
            privateSession: 'Privatna sesija',
            submit: 'Prijavi se',
            newHere: 'Novi si ovde?',
            registerPrompt:
                'Napravi nalog da bi upravljao budžetom, računima, kategorijama i ponavljajućim plaćanjima na jednom mestu.',
            registerLink: 'Napravi nalog',
        },
        register: {
            head: 'Registracija',
            eyebrow: 'Kreiraj profil',
            title: 'Otvori nalog i počni da organizuješ svaki trošak.',
            description:
                'Podesi svoj prostor za minut i dobićeš jasniji pregled stanja, kategorija, ponavljanja i trendova.',
            panelBadge: 'Novi nalog',
            panelTitle:
                'Od rasutih transakcija do jednog modernog finansijskog toka.',
            panelDescription:
                'Napravi lični finansijski sistem sa pregledom računa, pravilima ponavljanja i izveštajima koje je lako razumeti.',
            stats: {
                setup: 'Brzo otvaranje naloga',
                overview: 'Pregled sve potrošnje',
                recurring: 'Praćenje ponavljanja',
            },
            name: 'Ime i prezime',
            email: 'Email adresa',
            password: 'Lozinka',
            confirmPassword: 'Potvrdi lozinku',
            submit: 'Kreiraj nalog',
            alreadyHaveAccount: 'Već imaš nalog?',
            loginLink: 'Idi na prijavu',
        },
        forgotPassword: {
            head: 'Zaboravljena lozinka',
            eyebrow: 'Oporavak lozinke',
            title: 'Vrati pristup bez gubitka kontrole nad finansijama.',
            description:
                'Unesi email adresu i poslacemo ti bezbedan link za novu lozinku i povratak na tablu.',
            panelBadge: 'Oporavak pristupa',
            panelTitle:
                'Bezbedan oporavak naloga u istom preglednom okruženju.',
            panelDescription:
                'Vrati se računima, ponavljanjima i izveštajima kroz čist tok promene lozinke koji prati ostatak proizvoda.',
            stats: {
                link: 'Zaštićen recovery link',
                fast: 'Brz povratak na tablu',
                clear: 'Bez zabune i viška koraka',
            },
            email: 'Email adresa',
            submit: 'Pošalji link za reset lozinke',
            manualPrompt: 'Želiš radije ručnu prijavu?',
            loginLink: 'Nazad na prijavu',
        },
        resetPassword: {
            head: 'Reset lozinke',
            eyebrow: 'Postavi novu lozinku',
            title: 'Kreiraj novu lozinku i vrati se u radni tok.',
            description:
                'Izaberi jaku lozinku da obnovis pristup svom Troskovi prostoru i nastavis gde si stao.',
            panelBadge: 'Bezbedan reset',
            panelTitle: 'Gladak korak izmedju reset linka i punog pristupa.',
            panelDescription:
                'Zastiti finansijske podatke dok obnavljas pristup kroz iskustvo uskladjeno sa prijavom i registracijom.',
            stats: {
                protected: 'Oporavak preko tokena',
                strong: 'Nova jaka lozinka',
                back: 'Brz povratak u radni prostor',
            },
            email: 'Email',
            password: 'Lozinka',
            confirmPassword: 'Potvrdi lozinku',
            submit: 'Resetuj lozinku',
        },
        verifyEmail: {
            head: 'Verifikacija emaila',
            eyebrow: 'Potvrda email adrese',
            title: 'Potvrdi email i otključaj ceo radni prostor.',
            description:
                'Poslali smo verifikacioni link u tvoje sanduče. Kada potvrdiš adresu, nastavljaš sa punim pristupom nalogu.',
            panelBadge: 'Još jedan korak',
            panelTitle:
                'Verifikacija ostaje jednostavna, bezbedna i usklađena sa ostatkom proizvoda.',
            panelDescription:
                'Otvori poruku, potvrdi adresu i vrati se na čistu finansijsku tablu bez dodatnog trenja.',
            stats: {
                secure: 'Zaštićeno podešavanje naloga',
                quick: 'Jedna email potvrda',
                ready: 'Spremno za nastavak',
            },
            sentMessage:
                'Novi verifikacioni link je poslat na email adresu koju si uneo tokom registracije.',
            submit: 'Pošalji verifikacioni email ponovo',
            logoutPrompt: 'Pogrešan nalog ili želiš da izađeš iz ovog toka?',
            logout: 'Odjavi se',
        },
        confirmPassword: {
            head: 'Potvrda lozinke',
            eyebrow: 'Bezbednosna provera',
            title: 'Potvrdi lozinku pre ulaska u ovu zasticenu oblast.',
            description:
                'Ovaj dodatni korak cuva tvoje finansijske podatke pre nastavka ka osetljivim akcijama.',
            panelBadge: 'Zasticena oblast',
            panelTitle: 'Kratka bezbednosna provera pre nastavka.',
            panelDescription:
                'Potvrdi trenutnu lozinku da bi podešavanja naloga i osetljivi finansijski detalji ostali iza dodatne zaštite.',
            stats: {
                safe: 'Dodatni korak potvrde',
                private: 'Zaštićene osetljive radnje',
                fast: 'Brz povratak pristupa',
            },
            password: 'Lozinka',
            submit: 'Potvrdi lozinku',
        },
        twoFactor: {
            head: 'Dvofaktorska autentifikacija',
            eyebrow: 'Dodatna potvrda',
            panelBadge: 'Dodatna verifikacija',
            panelTitle: 'Osetljiv pristup ostaje zaštićen uz čist drugi korak.',
            panelDescription:
                'Koristi kod iz autentifikatora ili recovery kod za nastavak bez nepotrebnog opterećenja toka prijave.',
            stats: {
                app: 'Podrška za autentifikator',
                backup: 'Rezervni recovery kod',
                secure: 'Slojevita zaštita naloga',
            },
            recoveryTitle: 'Recovery kod',
            recoveryDescription:
                'Potvrdi pristup nalogu unosom jednog od svojih hitnih recovery kodova.',
            recoveryToggle: 'prijavu preko autentikacionog koda',
            authTitle: 'Autentikacioni kod',
            authDescription:
                'Unesi autentikacioni kod koji prikazuje tvoja aplikacija za autentifikaciju.',
            authToggle: 'prijavu preko recovery koda',
            continue: 'Nastavi',
            alternate: 'ili možeš da koristiš jedan od svojih recovery kodova.',
            recoveryPlaceholder: 'Unesi recovery kod',
        },
    },
    app: {
        nav: {
            dashboard: 'Pregled',
            bankAccounts: 'Bankovni računi',
            expenses: 'Troškovi',
            income: 'Prihodi',
            recurring: 'Ponavljanja',
            reports: 'Izveštaji',
            categories: 'Kategorije',
            repository: 'Repozitorijum',
            documentation: 'Dokumentacija',
            navigationMenu: 'Meni navigacije',
        },
    },
    common: {
        actions: {
            add: 'Dodaj',
            create: 'Kreiraj',
            update: 'Ažuriraj',
            edit: 'Izmeni',
            delete: 'Obriši',
            cancel: 'Otkaži',
            close: 'Zatvori',
            continue: 'Nastavi',
            back: 'Nazad',
            confirm: 'Potvrdi',
            view: 'Prikaži',
            hide: 'Sakrij',
            clearFilters: 'Očisti filtere',
            filters: 'Filteri',
            regenerate: 'Generiši ponovo',
            transfer: 'Prenos',
        },
        states: {
            loading: 'Učitavanje...',
            none: 'Nijedan',
            noneFeminine: 'Nijedna',
            anyStart: 'Bilo koji početak',
            anyEnd: 'Bilo koji kraj',
            uncategorized: 'Bez kategorije',
            noNotes: 'Nema dodatnih napomena',
            noLinkedAccount: 'Nema povezanog računa',
            cashWallet: 'Keš novčanik',
            never: 'Nikada',
            waitingData: 'Čekanje podataka',
            noDataYet: 'Još nema podataka',
        },
        labels: {
            overview: 'Pregled',
            amount: 'Iznos',
            date: 'Datum',
            description: 'Opis',
            category: 'Kategorija',
            categories: 'Kategorije',
            account: 'Račun',
            bankAccount: 'Bankovni račun',
            notes: 'Napomene',
            type: 'Tip',
            paymentMethod: 'Način plaćanja',
            frequency: 'Učestalost',
            nextDate: 'Sledeći datum',
            from: 'Od',
            to: 'Do',
            totalShown: 'Ukupno prikazano',
            filteredRows: 'Filtrirani redovi',
            categorized: 'Kategorisano',
            dateRange: 'Raspon datuma',
            transactions: 'Transakcije',
            resultsTotal: ':count ukupno rezultata',
            connectedAccounts: ':count povezanih računa',
            shownRange: 'Prikazano :from-:to od :total',
        },
        paymentMethods: {
            cash: 'Keš',
            bankAccount: 'Bankovni račun',
        },
        recurringFrequencies: {
            daily: 'Dnevno',
            weekly: 'Nedeljno',
            monthly: 'Mesečno',
            dailyLower: 'dnevno',
            weeklyLower: 'nedeljno',
            monthlyLower: 'mesečno',
        },
    },
    components: {
        alertError: {
            title: 'Došlo je do greške.',
        },
        twoFactorSetup: {
            enabledTitle: 'Dvofaktorska autentifikacija je uključena',
            enabledDescription:
                'Dvofaktorska autentifikacija je sada aktivna. Skeniraj QR kod ili unesi ključ za podešavanje u aplikaciju za autentifikaciju.',
            verifyTitle: 'Potvrdi autentikacioni kod',
            verifyDescription:
                'Unesi šestocifreni kod iz aplikacije za autentifikaciju.',
            enableTitle: 'Uključi dvofaktorsku autentifikaciju',
            enableDescription:
                'Da završiš uključivanje, skeniraj QR kod ili unesi ključ za podešavanje u aplikaciju za autentifikaciju.',
            manualEntry: 'ili unesi kod ručno',
        },
        twoFactorRecovery: {
            title: '2FA recovery kodovi',
            description:
                'Recovery kodovi ti vraćaju pristup ako izgubiš 2FA uređaj. Sačuvaj ih u bezbednom password manager-u.',
            toggleView: 'Prikaži recovery kodove',
            toggleHide: 'Sakrij recovery kodove',
            regenerate: 'Generiši kodove ponovo',
            footnote:
                'Svaki recovery kod može da se iskoristi jednom za pristup nalogu i uklanja se nakon upotrebe. Ako ti treba još kodova, klikni na Generiši kodove ponovo iznad.',
        },
        transactionForm: {
            badge: 'Uređivanje transakcije',
            newTitle: 'Nova transakcija',
            editTitle: 'Izmeni transakciju',
            editDescription:
                'Ažuriraj iznos, tok računa i kategoriju bez izlaska iz evidencije.',
            createDescription:
                'Unesi novu prihodnu ili troškovnu stavku sa istom strukturom kao na ostalim finansijskim ekranima.',
            typeDescription:
                'Izaberi da li ova stavka predstavlja priliv ili odliv novca.',
            expenseTitle: 'Trošak',
            expenseSubtitle: 'Gotovinski odliv ili kupovina',
            incomeTitle: 'Prihod',
            incomeSubtitle: 'Plata, uplata ili priliv',
            descriptionPlaceholder: 'Za šta je ova stavka?',
            selectCategory: 'Izaberi kategoriju',
            incomeAccountFlowTitle: 'Prihod ide kroz tok računa',
            incomeAccountFlowDescription:
                'Prihodi podrazumevano koriste bankovni račun kako bi stanje i izveštaji ostali usklađeni.',
            selectAccountOptional: 'Izaberi račun (opciono)',
            notesPlaceholder: 'Dodatne napomene...',
            bookingPreview: 'Pregled knjiženja',
            bookingExpenseCash:
                'Ovaj trošak će biti evidentiran kao keš odlazak.',
            bookingExpenseBank:
                'Ovaj trošak će umanjiti tok izabranog bankovnog računa.',
            bookingIncome: 'Ovaj prihod će uvećati tok izabranog računa.',
            created: 'Transakcija je kreirana',
            updated: 'Transakcija je ažurirana',
            saveError: 'Čuvanje transakcije nije uspelo',
        },
        recurringForm: {
            badge: 'Pravilo automatizacije',
            newTitle: 'Novo ponavljanje',
            editTitle: 'Izmeni ponavljanje',
            description:
                'Podesi sablon za ponavljajući prihod ili trošak sa istom strukturom kao i na ostalim finansijskim ekranima.',
            expenseSubtitle: 'Zakazano izlazno plaćanje',
            incomeSubtitle: 'Zakazano ulazno plaćanje',
            descriptionPlaceholder: 'npr. Netflix, Plata',
            selectCategory: 'Izaberi kategoriju',
            selectAccount: 'Izaberi račun',
            rulePreview: 'Pregled pravila',
            ruleSentence:
                'Ovo pravilo će se ponavljati :frequency počev od :date.',
            created: 'Ponavljajuća transakcija je kreirana',
            updated: 'Ponavljajuća transakcija je ažurirana',
            saveError: 'Čuvanje ponavljajuće transakcije nije uspelo',
        },
    },
    finance: {
        expenses: {
            head: 'Troškovi',
            badge: 'Kontrola troškova',
            heroTitle:
                'Troškovi koji ostaju pregledni i kada mesec postane haotičan.',
            heroDescription:
                'Brze pretrazi, izdvoji način plaćanja i drži sve izlazne transakcije u jednom doslednom pregledu.',
            averageExpense: 'Prosečan trošak',
            paidFromAccount: 'Plaćeno sa računa',
            recordsTitle: 'Evidencija troškova',
            recordsDescription:
                'Filtriraj, pregledaj i ažuriraj izlazne transakcije.',
            searchPlaceholder: 'Pretraži troškove...',
            add: 'Dodaj trošak',
            allCategories: 'Sve kategorije',
            allMethods: 'Svi načini',
            historyTitle: 'Istorija troškova',
            emptyTitle: 'Nema pronađenih troškova',
            emptyDescription:
                'Nijedan trošak ne odgovara filterima ili još nisi uneo nijedan zapis.',
            expenseLabel: 'Trošak',
            deleteTitle: 'Obriši trošak',
            deleteDescription:
                'Ovaj trošak će biti trajno obrisan. Ova radnja ne može da se opozove.',
            deleted: 'Trošak je obrisan',
            deleteError: 'Brisanje troška nije uspelo',
        },
        incomes: {
            head: 'Prihodi',
            badge: 'Tok prihoda',
            heroTitle:
                'Prihodi sa istom preglednošću kao i sažetak na početnoj tabli.',
            heroDescription:
                'Pregledaj šta je stiglo, kojoj kategoriji pripada i na koji račun je leglo bez prelaska na druge ekrane.',
            averageIncome: 'Prosečan prihod',
            linkedAccount: 'Povezan račun',
            recordsTitle: 'Evidencija prihoda',
            recordsDescription:
                'Drži ulazni novac organizovanim i lakim za pregled.',
            searchPlaceholder: 'Pretraži prihode...',
            add: 'Dodaj prihod',
            allCategories: 'Sve kategorije',
            historyTitle: 'Istorija prihoda',
            emptyTitle: 'Nema pronađenih prihoda',
            emptyDescription:
                'Nijedan prihod ne odgovara filterima ili još nisi uneo nijedan zapis.',
            incomeLabel: 'Prihod',
            deleteTitle: 'Obriši prihod',
            deleteDescription: 'Ovaj prihod će biti trajno obrisan.',
            deleted: 'Stavka prihoda je obrisana',
            deleteError: 'Brisanje prihoda nije uspelo',
        },
        reports: {
            head: 'Izveštaji',
            badge: 'Centar izveštaja',
            heroTitle:
                'Izveštaji koji povezuju trendove, kategorije i kretanje novca na jednom mestu.',
            heroDescription:
                'Promeni izveštajni period, uporedi prihode i troškove i istakni gde nastaje najveći pritisak na štednju.',
            reportRhythm: 'Ritam izveštaja',
            coveredPeriod: 'Obuhvaćeni period',
            monthlyChange: 'Mesečna promena',
            biggestExpense: 'Najveći trošak',
            savingsRate: 'Stopa štednje',
            currentPeriodNote: 'Na osnovu trenutnog :period perioda.',
            incomeVsExpenses: 'Prihodi naspram troškova',
            netBalanceOverTime: 'Neto stanje kroz vreme',
            expenseStructure: 'Struktura troškova',
            incomeStructure: 'Struktura prihoda',
            paymentStructure: 'Struktura plaćanja',
            cashVsBank: 'Keš naspram banke',
            basicComparison: 'Osnovno poređenje',
            balanceTrend: 'Trend stanja',
            structure: 'Struktura',
            expensesCategories: 'Kategorije troškova',
            incomeCategories: 'Kategorije prihoda',
            currentPeriod: 'Trenutni izveštajni period',
            retained: ':value% zadržano',
            top: 'Vrh: :value',
            totalIncome: 'Ukupni prihodi',
            totalExpenses: 'Ukupni troškovi',
            netSavings: 'Neto štednja',
            savingsRateKpi: 'Stopa štednje',
        },
        bankAccounts: {
            head: 'Bankovni računi',
            badge: 'Centar računa',
            heroTitle:
                'Bankovni računi i interni prenosi u jednom preglednom radnom prostoru.',
            heroDescription:
                'Pregledaj aktivna stanja, skloni arhivirane račune iz fokusa i premesti sredstva bez prekida finansijskog toka.',
            activeAccounts: 'Aktivni računi',
            archived: 'Arhivirani',
            totalNetworkBalance: 'Ukupno stanje mreže',
            banks: 'Banke',
            transfers: 'Prenosi',
            transfersDescription:
                'Premesti sredstva između računa bez napuštanja stranice.',
            visibility: 'Vidljivost',
            visibilityDescription:
                'Arhivirani računi ostaju van pregleda aktivnog stanja.',
            add: 'Dodaj račun',
            emptyTitle: 'Nema bankovnih računa',
            emptyDescription: 'Dodaj prvi bankovni račun da bi pratio stanja.',
            overviewTitle: 'Pregled računa',
            overviewDescription:
                'Aktivni bankovni računi i njihova trenutna stanja.',
            activeCount: ':count aktivnih',
            banksCount: ':count banka',
            currentBalance: 'Trenutno stanje',
            editMenu: 'Izmeni',
            archiveMenu: 'Arhiviraj',
            restore: 'Vrati',
            accountEditBadge: 'Uređivanje računa',
            editTitle: 'Izmeni bankovni račun',
            newTitle: 'Novi bankovni račun',
            editDescription:
                'Ažuriraj identitet računa, boju i početno stanje bez izlaska iz centra računa.',
            createDescription:
                'Dodaj novi bankovni račun za praćenje stanja, prenosa i izveštaja.',
            accountName: 'Naziv računa',
            accountNamePlaceholder: 'npr. Glavni tekući',
            bankName: 'Naziv banke',
            bankNamePlaceholder: 'npr. Intesa',
            accountNumber: 'Broj računa',
            optional: 'Opciono',
            currency: 'Valuta',
            initialBalance: 'Početno stanje',
            chooseColor: 'Izaberi boju',
            quickChoices: 'Brzi izbor',
            saving: 'Čuvanje...',
            created: 'Račun je kreiran',
            updated: 'Račun je ažuriran',
            saveError: 'Čuvanje računa nije uspelo',
            transferBadge: 'Tok prenosa',
            transferTitle: 'Prenos između računa',
            transferDescription:
                'Premesti novac između bankovnih računa bez napustanja pregleda računa.',
            fromAccount: 'Sa računa',
            toAccount: 'Na račun',
            selectAccount: 'Izaberi račun',
            transferDescriptionLabel: 'Opis',
            transferDescriptionPlaceholder: 'Razlog prenosa',
            transfering: 'Prenos u toku...',
            transferSuccess: 'Prenos je završen',
            transferError: 'Prenos nije uspeo',
            archiveTitle: 'Arhiviraj račun',
            archiveDescription:
                'Ovaj račun će biti sakriven iz aktivnih pregleda. Možeš ga vratiti kasnije.',
            archivedSuccess: 'Račun je arhiviran',
            archiveError: 'Arhiviranje računa nije uspelo',
            restoredSuccess: 'Račun je vraćen',
            restoreError: 'Vraćanje računa nije uspelo',
            detailBadge: 'Detalj računa',
            totalIncome: 'Ukupni prilivi',
            totalExpenses: 'Ukupni odlivi',
            bank: 'Banka',
            lastActivity: 'Poslednja aktivnost',
            noTransactionsYet: 'Još nema transakcija',
            history: 'Istorija računa',
            historyDescription: 'Transakcije vezane za ovaj račun.',
            displayedItems: ':count prikazanih stavki',
            loadingTransactions: 'čitavanje transakcija...',
            emptyTransactionsTitle: 'Nema transakcija',
            emptyTransactionsDescription:
                'Za ovaj račun još nema evidentiranih transakcija.',
        },
        categories: {
            head: 'Kategorije',
            badge: 'Biblioteka kategorija',
            heroTitle:
                'Održavaj sve troškovne i prihodovne grupe urednim, preglednim i lakim za održavanje.',
            heroDescription:
                'Kategorije oblikuju izveštaje, forme za transakcije i pregledne kartice. Ova stranica je sada usklađena sa ostatkom finansijskog prostora.',
            totalCategories: 'Ukupno kategorija',
            expenseGroups: 'Grupe troškova',
            incomeGroups: 'Grupe prihoda',
            activeView: 'Aktivan prikaz',
            system: 'Sistemske',
            custom: 'Prilagođene',
            managementTitle: 'Upravljanje kategorijama',
            managementDescription:
                'Odvoji izlazne i ulazne kategorije, pa njima upravljaj na jednom mestu.',
            add: 'Dodaj kategoriju',
            expenseTab: 'Troškovi (:count)',
            incomeTab: 'Prihodi (:count)',
            expenseLabel: 'Trošak',
            incomeLabel: 'Prihod',
            expenseCollection: 'Kategorije troškova',
            incomeCollection: 'Kategorije prihoda',
            expenseSectionDescription:
                'Napravljeno za preglednije izveštaje i brze označavanje transakcija.',
            incomeSectionDescription:
                'Održi dolazne tokove grupisanim u istoj strukturi kao i evidenciju prihoda.',
            totalCount: ':count ukupno',
            editableCount: ':count izmenjivih',
            emptyExpenseTitle: 'Nema kategorija troškova',
            emptyExpenseDescription:
                'Dodaj kategorije da bi bolje organizovao troškove.',
            emptyIncomeTitle: 'Nema kategorija prihoda',
            emptyIncomeDescription:
                'Dodaj kategorije da bi bolje organizovao prihode.',
            systemCategory: 'Sistemska kategorija',
            customCategory: 'Prilagođena kategorija',
            formBadge: 'Uređivanje kategorije',
            editTitle: 'Izmeni kategoriju',
            newTitle: 'Nova kategorija',
            editDescription:
                'Ažuriraj naziv, ikonicu i boju kategorije bez menjanja postojeće strukture stranice.',
            createDescription:
                'Kreiraj novu kategoriju koja će se pojavljivati u formama, izveštajima i filterima.',
            name: 'Naziv',
            namePlaceholder: 'npr. Namirnice',
            icon: 'Ikonica',
            color: 'Boja',
            visualPreview: 'Vizuelni pregled',
            categoryNameFallback: 'Naziv kategorije',
            expenseCategory: 'kategorija troška',
            incomeCategory: 'kategorija prihoda',
            chooseColor: 'Izaberi boju',
            quickChoices: 'Brzi izbor',
            created: 'Kategorija je kreirana',
            updated: 'Kategorija je ažurirana',
            saveError: 'Čuvanje kategorije nije uspelo',
            deleted: 'Kategorija je obrisana',
            deleteError: 'Brisanje kategorije nije uspelo',
            deleteTitle: 'Obriši kategoriju',
            deleteDescription:
                'Transakcije koje koriste ovu kategoriju ostaće bez kategorije.',
        },
        recurring: {
            head: 'Ponavljajuće transakcije',
            badge: 'Pravila automatizacije',
            heroTitle:
                'Ponavljajući prihodi i troškovi sa istom strukturom kao i ostatak evidencije.',
            heroDescription:
                'Drži platu, kiriju, pretplate i druge zakazane tokove vidljivim pre nego što uđu u istoriju transakcija.',
            expenseRules: 'Pravila troškova',
            incomeRules: 'Pravila prihoda',
            activeTab: 'Aktivni tab',
            automationEntry: 'Automatsko knjiženje',
            automationDescription:
                'Svako pravilo čuva vreme, kategoriju i tok računa.',
            control: 'Kontrola',
            controlDescription:
                'Deaktiviraj pravila bez uklanjanja već proknjižene istorije.',
            managementTitle: 'Upravljanje ponavljanjima',
            managementDescription:
                'Pregledaj, izmeni i deaktiviraj ponavljajuća pravila na jednom mestu.',
            add: 'Dodaj ponavljanje',
            expenseTitle: 'Trošak',
            incomeTitle: 'Prihod',
            expenseLabel: 'Ponavljajući troškovi',
            incomeLabel: 'Ponavljajući prihodi',
            expenseSectionDescription:
                'Zakazani odlivi pripremljeni pre nego što uđu u evidenciju.',
            incomeSectionDescription:
                'Predvidivi ulazni tokovi pripremljeni pre sledećeg datuma knjiženja.',
            availableAccounts: ':count dostupnih računa',
            emptyExpenseTitle: 'Nema ponavljajućih troškova',
            emptyExpenseDescription:
                'Ovde dodaj pretplate, kiriju ili druge ponavljajuće troškove.',
            emptyExpenseAction: 'Dodaj ponavljajući trošak',
            emptyIncomeTitle: 'Nema ponavljajućih prihoda',
            emptyIncomeDescription:
                'Ovde dodaj platu, honorare ili druge ponavljajuće prihode.',
            emptyIncomeAction: 'Dodaj ponavljajući prihod',
            lastProcessed: 'Poslednje obrađeno: :date',
            deactivated: 'Ponavljajuća transakcija je deaktivirana',
            deactivateError:
                'Deaktivacija ponavljajuće transakcije nije uspela',
            deactivateTitle: 'Deaktiviraj ponavljanje',
            deactivateDescription:
                'Ovo pravilo više neće praviti buduće transakcije, ali postojeća istorija ostaje sačuvana.',
            deactivateConfirm: 'Deaktiviraj',
        },
    },
    dashboard: {
        head: 'Pregled',
        badge: 'Troškovi pregled',
        title: 'Čistiji pregled tvog novca, računa i mesečnog kretanja.',
        description:
            'Drži stanja, skoršnju aktivnost i učinke štednje u jednom fokusiranom prikazu koji pomaže da reaguješ brže i planiraš bolje.',
        reportsTitle: 'Izveštaji',
        reportsDescription: 'Pregledaj kategorije, trendove i tempo štednje.',
        accountsTitle: 'Računi',
        accountsDescription: 'Brzo prati stanja i aktivnost po računima.',
        kpis: {
            totalIncome: 'Ukupni prihodi',
            totalExpenses: 'Ukupni troškovi',
            netSavings: 'Neto štednja',
            savingsRate: 'Stopa štednje',
            thisMonth: 'Ovog meseca',
            topExpense: 'Najveći trošak: :value',
        },
        recentTransactions: {
            title: 'Skorašnje transakcije',
            description: 'Poslednja kretanja novca kroz tvoje kategorije.',
            viewAll: 'Vidi sve',
            empty: 'Još nema transakcija. Dodaj prvu.',
            uncategorized: 'Bez kategorije',
        },
        accounts: {
            title: 'Bankovni računi',
            description: 'Trenutna stanja i povezane institucije.',
            manage: 'Upravljaj',
            empty: 'Još nema dodatih bankovnih računa.',
        },
    },
    settings: {
        common: {
            settings: 'Podešavanja',
            save: 'Sačuvaj',
            saved: 'Sačuvano.',
            cancel: 'Otkaži',
        },
        nav: {
            profile: 'Profil',
            security: 'Bezbednost',
            appearance: 'Izgled',
            description:
                'Upravljaj profilom, bezbednošću i izgledom interfejsa iz jednog mesta.',
        },
        profile: {
            head: 'Podešavanja profila',
            title: 'Informacije o profilu',
            description: 'Azuriraj ime i email adresu',
            name: 'Ime i prezime',
            email: 'Email adresa',
            fullName: 'Puno ime',
            emailPlaceholder: 'Email adresa',
            emailUnverified: 'Tvoja email adresa nije verifikovana.',
            resend: 'Klikni ovde da ponovo posaljes verifikacioni email.',
            linkSent:
                'Novi verifikacioni link je poslat na tvoju email adresu.',
        },
        security: {
            head: 'Podešavanja bezbednosti',
            title: 'Ažuriranje lozinke',
            description:
                'Koristi dugu i nasumičnu lozinku kako bi nalog ostao bezbedan',
            currentPassword: 'Trenutna lozinka',
            newPassword: 'Nova lozinka',
            confirmPassword: 'Potvrdi lozinku',
            savePassword: 'Sačuvaj lozinku',
            twoFactorTitle: 'Dvofaktorska autentifikacija',
            twoFactorDescription:
                'Upravljaj podešavanjima dvofaktorske autentifikacije',
            twoFactorIntro:
                'Kada uključiš dvofaktorsku autentifikaciju, pri prijavi će biti zatražena bezbedna šifra iz TOTP aplikacije na telefonu.',
            continueSetup: 'Nastavi podešavanje',
            enable2fa: 'Uključi 2FA',
            twoFactorEnabled:
                'Pri prijavi će biti zatražen bezbedan kod koji možeš pročitati iz TOTP aplikacije na telefonu.',
            disable2fa: 'Isključi 2FA',
        },
        appearance: {
            head: 'Podešavanja izgleda',
            title: 'Podešavanja izgleda',
            description: 'Ažuriraj izgled svog naloga',
            light: 'Svetla',
            dark: 'Tamna',
            system: 'Sistemska',
        },
        deleteAccount: {
            title: 'Brisanje naloga',
            description: 'Obriši nalog i sve povezane podatke',
            warning: 'Upozorenje',
            warningText: 'Postupaj pažljivo, ovu radnju nije moguće opozvati.',
            trigger: 'Obriši nalog',
            dialogTitle: 'Da li sigurno želiš da obrišeš nalog?',
            dialogDescription:
                'Kada se nalog obriše, svi povezani resursi i podaci biće trajno uklonjeni. Unesi lozinku da potvrdiš trajno brisanje naloga.',
            password: 'Lozinka',
            confirm: 'Obriši nalog',
        },
    },
} satisfies MessageDictionary;

function resolveMessage(path: string): string | undefined {
    const result = path
        .split('.')
        .reduce<MessageValue | undefined>((current, segment) => {
            if (!current || typeof current === 'string') {
                return undefined;
            }

            return current[segment];
        }, messages);

    return typeof result === 'string' ? result : undefined;
}

export function t(
    key: string,
    params?: Record<string, string | number>,
): string {
    const message = resolveMessage(key) ?? key;

    if (!params) {
        return message;
    }

    return Object.entries(params).reduce((current, [paramKey, value]) => {
        return current.replaceAll(`:${paramKey}`, String(value));
    }, message);
}

export function formatCurrency(amount: number, currency = 'RSD'): string {
    return amount.toLocaleString('sr-RS', {
        style: 'currency',
        currency,
    });
}

export function formatShortDate(date: string): string {
    return new Date(date).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
    });
}
