## Summary

<!-- What does this PR change and why? -->

## Screenshots / Demo

<!-- For UI changes, add before/after screenshots or a short clip. -->

## Testing

<!-- How was this verified? List commands run and scenarios covered. -->

```bash
# backend
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse

# frontend
npm run lint
npm run type-check
npm run build
```

## Checklist

- [ ] Tests pass (`php artisan test`)
- [ ] Static analysis passes (`./vendor/bin/phpstan analyse`, `./vendor/bin/pint --test`)
- [ ] Frontend builds (`npm run lint`, `npm run type-check`, `npm run build`)
- [ ] Documentation updated if needed
