# Node toolchain for the package: pnpm, Vite build, Vitest, tsc, ESLint and
# Playwright. The official Playwright image ships the browsers and all system
# libraries they need, so E2E runs without extra setup.
#
# IMPORTANT: keep this tag in sync with the resolved `@playwright/test` version
# in pnpm-lock.yaml. The image bakes in browser binaries for a specific
# Playwright version; if the npm package drifts ahead, the bundled browsers no
# longer match and `playwright test` fails with an "Executable doesn't exist"
# error. When bumping @playwright/test, bump this tag to the same version.
FROM mcr.microsoft.com/playwright:v1.61.0-jammy

# Enable Corepack-managed pnpm (pinned to the version in package.json).
RUN corepack enable && corepack prepare pnpm@9.12.3 --activate

ENV PNPM_HOME=/home/pwuser/.local/share/pnpm \
    PATH="/home/pwuser/.local/share/pnpm:${PATH}" \
    CI=1

WORKDIR /app

CMD ["bash"]
