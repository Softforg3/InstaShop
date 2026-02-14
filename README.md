## Otrzymałeś zadanie, co teraz?
- Przeczytaj uważnie to readme.
- Od razu stwórz repozytorium git. Modyfikacje kodu muszą być widoczne w historii zmian.

## Jak oceniamy zadanie?
Wiemy, jak trudne jest rozwiązywanie zadania rekrutacyjnego, gdy nie wiesz, jakie są kryteria oceny takiego zadania. W tym zadaniu zależy nam głównie na ocenie Twoich umiejętności. Nie będziemy oceniać tego w taki sam sposób, jak robimy to przy Code Review. Aczkolwiek nie traktuj zadania jako czegoś, co możesz odbębnić. Pracujemy na projektach, które rozwijane są od jakiegoś czasu i będą rozwijane jeszcze długo.
Wyobraź sobie, że ten projekt również będzie rozwijany przez kilka lat i przez kilku developerów.

Możesz wybrać ścieżkę KISS oraz opisać, co można by zrobić, by kod był łatwiejszy w rozbudowie. Lub możesz zastosować lekki overengineering, by pokazać w praktyce swoje umiejętności i opisać, dlaczego w tym przypadku nie warto.

Zależy nam na sprawdzeniu, czy znasz dobre praktyki programowania np. KISS, SOLID, DRY, TDD, F.I.R.S.T., Boy Scout Rule, i jak je stosujesz w praktyce. Jakie znasz techniki architektoniczne? Może da się tutaj coś poprawić?

Spodziewamy się, że napiszesz kilka testów. Nie cała aplikacja musi być przetestowana. Pochwal się tym, jakie testy znasz i napisz przynajmniej jeden test z każdego rodzaju.

## Twój komentarz
Twoimi przemyśleniami możesz dzielić się w `docs/NOTES.md`. Możesz zawrzeć tam np.

- Opis wprowadzonych zmian i podjętych decyzji architektonicznych
- Rzeczy, które zrobiłbyś inaczej mając więcej czasu
- Napotkane problemy i sposób ich rozwiązania
- Propozycje usprawnień, których nie zdążyłeś zaimplementować
- Informacje o sposobie i stopniu wykorzystania AI

Plik ten pomoże nam lepiej zrozumieć Twój tok myślenia.
Komentarze w kodzie też są okej.

## Zadanie
SymfonyApp to aplikacja, która pozwala użytkownikom na dzielenie się swoimi zdjęciami.
Jest we wczesnym etapie rozwoju i zawiera kilka podstawowych funkcjonalności.
Są to:
- Wyświetlanie galerii zdjęć na stronie głównej. Każdy kafelek zawiera podstawowe informacje oraz ilość polubień.
- Like/unlike zdjęć.
- Logowanie za pomocą tokenu oraz możliwość wylogowania.
- Wyświetlenie profilu.

### Zadanie 1 - zadbaj o jakość kodu oraz rozwiązań w projekcie SymfonyApp.
Znajdź błędy, a następnie nanieś poprawki lub zasugeruj zmianę.
Upewnij się, że projekt ma dobre fundamenty pod dalszy rozwój - struktura kodu musi być łatwa do zrozumienia dla nowych programistów.

### Zadanie 2 - Dodaj funkcjonalność importu zdjęć do SymfonyApp z PhoenixApi.
PhoenixApi to aplikacja, która przechowuje zdjęcia z innych aplikacji partnerskich, z których korzystają użytkownicy SymfonyApp. Wystawiony jest endpoint, za pomocą którego można pobrać zdjęcia używając tokenu dostępu.

W aplikacji SymfonyApp należy dać użytkownikom możliwość ręcznego wpisania tokenu dostępu do PhoenixApi (w profilu użytkownika). Token powinien zostać zapisany w bazie danych.
Następnie, po naciśnięciu przycisku "Importuj zdjęcia", zdjęcia z PhoenixApi powinny zostać zaimportowane do SymfonyApp jako zdjęcia tego użytkownika. 
W przypadku błędnego tokenu, należy wyświetlić odpowiedni komunikat.

### Zadanie 3 - Filtrowanie zdjęć na stronie głównej.
Użytkownicy SymfonyApp muszą mieć możliwość filtrowania zdjęć po następujących polach:
- location
- camera
- description
- taken_at
- username

## Architektura

Ten projekt składa się z dwóch oddzielnych aplikacji z własnymi bazami danych:

- **Symfony App** (port 8000): Główna aplikacja internetowa
  - Baza danych: `symfony-db` (PostgreSQL, port 5432)
  - Nazwa bazy danych: `instashot`

- **Phoenix API** (port 4000): Mikroserwis REST API
  - Baza danych: `phoenix-db` (PostgreSQL, port 5433)
  - Nazwa bazy danych: `phoenix_api`

## Szybki start
```bash
docker-compose up -d

# Konfiguracja bazy danych Symfony
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec symfony php bin/console app:seed

# Konfiguracja bazy danych Phoenix
docker-compose exec phoenix mix ecto.migrate
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

Dostęp do aplikacji:
- Symfony App: http://localhost:8000
- Phoenix API: http://localhost:4000

## Komendy Symfony

### Migracja bazy danych
```bash
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
```

### Ponowne tworzenie bazy danych
```bash
docker-compose exec symfony php bin/console doctrine:schema:drop --force --full-database
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec symfony php bin/console app:seed
```

### Czyszczenie pamięci podręcznej (Cache)
```bash
docker-compose exec symfony php bin/console cache:clear
```

### Restart
```bash
docker-compose restart symfony
```

### Uruchamianie testów
```bash
docker-compose exec symfony php bin/phpunit
```

## Komendy Phoenix

### Migracja bazy danych
```bash
docker-compose exec phoenix mix ecto.migrate
```

### Seedowanie bazy danych
```bash
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Ponowne tworzenie bazy danych
```bash
docker-compose exec phoenix mix ecto.reset
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Restart
```bash
docker-compose restart phoenix
```

### Uruchamianie testów
```bash
docker-compose exec phoenix mix test
```
