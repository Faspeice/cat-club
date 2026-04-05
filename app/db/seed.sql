-- Seed data (demo)

INSERT INTO users (nick, contact, password_hash)
VALUES
  ('lubluKoshek', 'test@example.com', '$2y$10$v7T0n7XfSyR7VjYb8BEOvO/7sGf3sK4P4lq4u7Q7mTtJQhQxVfQXq'),
  ('catOwner2', 'owner2@example.com', '$2y$10$Q1yq9bq0E5pQz8t0I1tF1O8uF3zQm2kK8mQb9O8Jx8o2oYyGxvP8e')
ON CONFLICT DO NOTHING;

-- NOTE: password hashes above are placeholders; you can re-seed with real ones via registration.

INSERT INTO pets (owner_id, name, breed, age, photo_url, story)
SELECT u.id, 'Сэр Николас I', 'Сиамская кошка', 9,
  'https://images.unsplash.com/photo-1513245543132-31f507417b26?auto=format&fit=crop&w=900&q=80',
  'Сэр Николас — барон фон ободранных обоев.'
FROM users u WHERE u.nick='lubluKoshek'
ON CONFLICT DO NOTHING;

INSERT INTO topics (title, slug)
VALUES
  ('Питание', 'pitaniye'),
  ('Юмор', 'yumor')
ON CONFLICT DO NOTHING;

INSERT INTO posts (user_id, topic_id, title, body, photo_url)
SELECT u.id, t.id, 'Мой кот любит бананы!', 'Внезапно оказалось, что мой кот обожает бананы.', 'https://placekitten.com/600/300'
FROM users u, topics t
WHERE u.nick='lubluKoshek' AND t.slug='yumor'
ON CONFLICT DO NOTHING;

INSERT INTO events (title, body, place, starts_at, ends_at)
VALUES
  ('Встреча клуба в парке', 'Неформальная встреча владельцев и питомцев.', 'г. Омск, парк «Зелёный остров»', NOW() + INTERVAL '3 day', NOW() + INTERVAL '3 day' + INTERVAL '2 hours')
ON CONFLICT DO NOTHING;

