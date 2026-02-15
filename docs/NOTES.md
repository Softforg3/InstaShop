# Notatki — Insta Shot

## Co znalazłem w kodzie

1. **SQL Injection w AuthController** — surowe zapytania ze sklejaniem stringów, klasyczna podatność. Naprawiłem na `findOneBy()`.
2. **Brak DI** — kontrolery tworzyły repozytoria przez `new` zamiast wstrzykiwania. Łamanie SOLID.
3. **Kontrolery robiły za dużo** — jeden kontroler na wiele akcji, logika biznesowa pomieszana z HTTP. Rozbiłem na single-action controllers + CQRS (Command/Query handlers).
4. **LikeRepository miał mutowalny stan** — `setUser()` trzymał użytkownika w polu klasy. Repo powinno być bezstanowe, user idzie jako parametr.
5. **Brak transakcji** — like tworzył rekord i aktualizował licznik jako dwie osobne operacje. Owinąłem w `wrapInTransaction()`.
6. **Brak UNIQUE na likes** — tabela pozwala na duplikaty (user_id, photo_id). Naprawione — dodana migracja z deduplikacją istniejących danych.
7. **Auth nie sprawdzał powiązania token-user** — można było zalogować się na cudze konto. Naprawione.
8. **Generyczne wyjątki** — `catch (\Throwable)` tracił oryginalny błąd. Zastąpiłem domenowymi wyjątkami.
9. **Brak testów** w Symfony (Phoenix API ma 6 testów).

## Co pozytywnego

- Moduł Likes z interfejsem repozytorium — dobra modularyzacja
- Zdenormalizowany `like_counter` — pragmatyczne podejście do wydajności
- Encje Doctrine OK, relacje poprawne

## Wprowadzone zmiany (Zadanie 1)

- CQRS — Command/Handler (Login, LikePhoto, UnlikePhoto) + Query/Handler (GetGallery, GetProfile)
- CommandBus i QueryBus z tagged services — kontrolery nie znają konkretnych handlerów. W większym projekcie busy przenieślibyśmy do Infrastructure (bo to techniczna warstwa dispatcha), ale tutaj trzymam je w CQRS razem z handlerami — mniej plików do przeskakiwania, łatwiej ogarnąć całość
- Single-action controllers z `__invoke()`
- Domenowe wyjątki zamiast generycznych
- Stateless LikeRepository z transakcjami
- Fix SQL injection i walidacji auth

## Wprowadzone zmiany (Zadanie 2)

- Import zdjęć z Phoenix API przez PhoenixClient (Ports & Adapters)
- PhoenixPhotoDto + PhoenixPhotoCollection jako typowana kolekcja DTO
- Formularz tokena i przycisk importu na profilu
- Deduplikacja — nie importuje zdjęć które już istnieją

## Wprowadzone zmiany (Zadanie 3)

- Filtrowanie galerii po: location, camera, description, username, zakres dat (taken_at)
- Strategy Pattern z tagged services — każdy typ filtra to osobna klasa implementująca `PhotoFilterInterface`, zbierane automatycznie przez `PhotoFilterRegistry`
- GalleryFilterDto jako Value Object z `fromRequest()` — czyste mapowanie HTTP → domena
- Formularz GET z zachowywaniem wartości filtrów po odświeżeniu

Filtry budują `FilterCriteriaCollection` (pole + operator + wartość) bez wiedzy o Doctrine. Dopiero repozytorium mapuje kryteria na QueryBuilder. Dzięki temu gdyby w przyszłości galeria przeszła np. na Elasticsearch, wystarczy napisać nowy adapter mapujący te same kryteria na zapytania ES — strategie filtrów zostają bez zmian.

Strategia to tu lekki overkill — przy kilku prostych filtrach tekstowych wystarczyłoby parę `if`-ów w repozytorium. Ale chciałem pokazać pattern, który zaczyna się opłacać przy bardziej złożonych filtrach (full-text, geo, zakresy cenowe). Dodanie nowego filtra = nowa klasa, zero zmian w istniejącym kodzie.

## Co bym zrobił gdybym miał więcej czasu

- **Paginacja z lazy loading** — galeria powinna ładować zdjęcia stronami zamiast wszystkich naraz. Cursor-based pagination (po id/dacie) zamiast offset, bo nie gubi pozycji przy dodawaniu nowych zdjęć. Infinite scroll na froncie z Intersection Observer — użytkownik scrolluje, kolejna strona doładowuje się automatycznie
- **Redis cache na pierwszą stronę** — najczęściej odwiedzana strona galerii to pierwsza. Trzymanie wyniku w Redis z dłuższym TTL (np. 10-15 min) odciąża bazę. Invalidacja event-driven — po imporcie zdjęć, dodaniu like'a itp. kasujemy klucz cache i od razu robimy cache warmup (odbudowujemy cache nowym zapytaniem), żeby następny użytkownik trafił w gotowy wynik zamiast czekać na cold miss
- **Elasticsearch do full-text search** — obecne filtrowanie LIKE po PostgreSQL działa, ale nie obsługuje stemming, fuzzy matching ani relevance scoring. ES pozwoliłby szukać po dowolnej frazie z uwzględnieniem synonimów, literówek i języka naturalnego. Obecna architektura z CriteriaCollection ułatwia migrację — wystarczy nowy adapter mapujący kryteria na zapytania ES zamiast QueryBuildera
- **Symfony Messenger (async import)** — import zdjęć z Phoenix API synchronicznie blokuje request. Przy większej liczbie zdjęć użytkownik czeka. Messenger pozwoliłby wrzucić `ImportPhotosMessage` na kolejkę (Redis/RabbitMQ), oddać response od razu, a import leci w tle przez workera
- **Domain Events** — `PhotosImported`, `PhotoLiked` itp. jako eventy dispatchowane po akcji. Na ten moment brak realnych side-effectów które by ich wymagały, więc byłoby to przygotowanie pod Open/Closed Principle — kiedy pojawią się wymagania typu wysyłka maila do autora po like'u, cache invalidation, audit log — wystarczy dopisać listener zamiast modyfikować istniejący kod
- **Makefile** — `make test`, `make seed`, `make reset-db`, `make import` zamiast długich komend `docker compose exec ...`. Ułatwia onboarding nowych devów i standaryzuje workflow
- **REST API** — JSON API do galerii obok widoków HTML (osobny prefix `/api/`). Serializer, pagination w response headers, content negotiation. Przygotowanie pod frontend SPA lub mobilkę
- **PHPStan + CS Fixer** — statyczna analiza na wysokim poziomie (level 8+) i wymuszony code style. W CI jako gate — PR nie przejdzie bez zielonego PHPStan i spójnego formatowania

## Napotkane problemy

- **PHP 8.1 vs `readonly class`** — odruchowo chciałem użyć `readonly class` na DTO, ale to feature 8.2. Trzeba było `readonly` na poszczególnych properties zamiast na klasie
- **Brak `chmod +x` na entrypoint.sh w Dockerfile** — kontener Symfony nie startował, bo entrypoint nie miał uprawnień do wykonania. Dodanie `RUN chmod +x` w Dockerfile naprawiło problem
- **`docker-compose` vs `docker compose`** — README używał starej składni `docker-compose` (v1), która jest deprecated. Aktualizacja na `docker compose` (v2). Przy okazji usunięcie obsolete `version` z docker-compose.yml — nowszy Docker Compose go nie wymaga i rzuca warningiem

## Wykorzystanie AI

Przy realizacji zadania korzystałem z AI (Claude) jako narzędzia wspomagającego. AI pomagało w:

- **Generowaniu testów** — pisanie testów to dużo powtarzalnej pracy: przygotowanie fixtures, mockowanie zależności, kopiowanie setupu między test case'ami. AI generuje to w sekundy, ja weryfikuję i dopasowuję asercje do tego co faktycznie chcę testować
- **Tworzeniu migracji** — szybciej niż `doctrine:migrations:diff` czy ręczne pisanie SQL. AI widzi kontekst (encje, istniejące migracje) i od razu generuje migrację z edge case'ami (np. deduplikacja danych przed dodaniem UNIQUE)
- **Dyskusji nad architekturą** — konsultowanie decyzji typu: czy Strategy Pattern ma sens przy prostym filtrowaniu, gdzie umieścić CommandBus/QueryBus, jak oddzielić CriteriaCollection od QueryBuildera

Cały kod był przeze mnie review'owany i dostosowywany — AI przyspiesza pracę, ale decyzje architektoniczne i odpowiedzialność za jakość kodu pozostają po stronie developera.
