<?php

namespace App\Services;

use App\Models\CompanyTaxSetting;
use Illuminate\Support\Facades\Http;

class PdfBrandingService
{
    public function getHotelLogoDataUri(?string $fallbackPublicPath = null): ?string
    {
        static $cached = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }

        $resolved = true;
        $fallbackPublicPath = $fallbackPublicPath ?:
            public_path("assets/img/backgrounds/logo-Photoroom.png");

        $logoUrl = (string) (CompanyTaxSetting::query()->value("logo_url") ?? "");

        $resolvedImage = $this->resolveImageBinary($logoUrl);
        if ($resolvedImage === null) {
            $resolvedImage = $this->resolveImageBinary($fallbackPublicPath);
        }

        if ($resolvedImage === null) {
            $cached = null;
            return null;
        }

        [$binary, $mime] = $resolvedImage;
        $cached = "data:{$mime};base64," . base64_encode($binary);

        return $cached;
    }

    /**
     * @return array{0:string,1:string}|null
     */
    private function resolveImageBinary(?string $source): ?array
    {
        $source = trim((string) $source);
        if ($source === "") {
            return null;
        }

        if (
            preg_match(
                '/^data:(image\/[a-z0-9.+-]+);base64,(.+)$/i',
                $source,
                $matches,
            )
        ) {
            $decoded = base64_decode($matches[2], true);
            if ($decoded !== false && $decoded !== "") {
                return [$decoded, strtolower((string) $matches[1])];
            }
        }

        foreach ($this->buildLocalCandidates($source) as $candidate) {
            if (!is_file($candidate) || !is_readable($candidate)) {
                continue;
            }

            $binary = @file_get_contents($candidate);
            if ($binary === false || $binary === "") {
                continue;
            }

            return [$binary, $this->detectMimeFromPath($candidate)];
        }

        if (filter_var($source, FILTER_VALIDATE_URL)) {
            $path = parse_url($source, PHP_URL_PATH);
            if (is_string($path) && $path !== "") {
                $publicCandidate = public_path(ltrim($path, "/\\"));
                if (is_file($publicCandidate) && is_readable($publicCandidate)) {
                    $binary = @file_get_contents($publicCandidate);
                    if ($binary !== false && $binary !== "") {
                        return [$binary, $this->detectMimeFromPath($publicCandidate)];
                    }
                }
            }

            try {
                $response = Http::timeout(5)->retry(1, 150)->get($source);
                if ($response->successful()) {
                    $binary = (string) $response->body();
                    if ($binary !== "") {
                        $headerMime = strtolower(
                            trim(explode(";", (string) $response->header("Content-Type"))[0] ?? ""),
                        );
                        $mime = str_starts_with($headerMime, "image/")
                            ? $headerMime
                            : $this->detectMimeFromPath($source);

                        return [$binary, $mime];
                    }
                }
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * @return array<int,string>
     */
    private function buildLocalCandidates(string $source): array
    {
        $candidates = [];

        $isWindowsAbsolute = (bool) preg_match('/^[a-zA-Z]:\\\\/', $source);
        $isUnixAbsolute = str_starts_with($source, "/");

        if ($isWindowsAbsolute || $isUnixAbsolute) {
            $candidates[] = $source;
        }

        $candidates[] = public_path(ltrim($source, "/\\"));

        return array_values(array_unique($candidates));
    }

    private function detectMimeFromPath(string $path): string
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            "jpg", "jpeg" => "image/jpeg",
            "gif" => "image/gif",
            "webp" => "image/webp",
            "svg" => "image/svg+xml",
            default => "image/png",
        };
    }
}
