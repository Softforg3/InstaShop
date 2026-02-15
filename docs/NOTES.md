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

## Plan dalszych prac

### Testy

- Unit, integracyjne, funkcjonalne — po jednym z każdego rodzaju jak wymaga zadanie
