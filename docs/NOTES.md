# Notatki — Insta Shot

## Co znalazłem w kodzie

1. **SQL Injection w AuthController** — surowe zapytania ze sklejaniem stringów, klasyczna podatność. Naprawiłem na `findOneBy()`.
2. **Brak DI** — kontrolery tworzyły repozytoria przez `new` zamiast wstrzykiwania. Łamanie SOLID.
3. **Kontrolery robiły za dużo** — jeden kontroler na wiele akcji, logika biznesowa pomieszana z HTTP. Rozbiłem na single-action controllers + CQRS (Command/Query handlers).
4. **LikeRepository miał mutowalny stan** — `setUser()` trzymał użytkownika w polu klasy. Repo powinno być bezstanowe, user idzie jako parametr.
5. **Brak transakcji** — like tworzył rekord i aktualizował licznik jako dwie osobne operacje. Owinąłem w `wrapInTransaction()`.
6. **Brak UNIQUE na likes** — tabela pozwala na duplikaty (user_id, photo_id). Do dodania.
7. **Auth nie sprawdzał powiązania token-user** — można było zalogować się na cudze konto. Naprawione.
8. **Generyczne wyjątki** — `catch (\Throwable)` tracił oryginalny błąd. Zastąpiłem domenowymi wyjątkami.
9. **Brak testów** w Symfony (Phoenix API ma 6 testów).

## Co pozytywnego

- Moduł Likes z interfejsem repozytorium — dobra modularyzacja
- Zdenormalizowany `like_counter` — pragmatyczne podejście do wydajności
- Encje Doctrine OK, relacje poprawne

## Wprowadzone zmiany (Zadanie 1)

- CQRS — Command/Handler (Login, LikePhoto, UnlikePhoto) + Query/Handler (GetGallery, GetProfile)
- CommandBus i QueryBus z tagged services — kontrolery nie znają konkretnych handlerów
- Single-action controllers z `__invoke()`
- Domenowe wyjątki zamiast generycznych
- Stateless LikeRepository z transakcjami
- Fix SQL injection i walidacji auth

## Wprowadzone zmiany (Zadanie 2)

- Import zdjęć z Phoenix API przez PhoenixClient (Ports & Adapters)
- PhoenixPhotoDto + PhoenixPhotoCollection jako typowana kolekcja DTO
- Formularz tokena i przycisk importu na profilu
- Deduplikacja — nie importuje zdjęć które już istnieją

## Plan dalszych prac

### Zadanie 3 — Filtrowanie galerii

- Dynamiczne filtrowanie po: location, camera, description, taken_at (zakres dat), username
- Formularz GET nad galerią z zachowywaniem wartości filtrów

### Testy

- Unit, integracyjne, funkcjonalne — po jednym z każdego rodzaju jak wymaga zadanie
