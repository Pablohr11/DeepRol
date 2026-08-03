<?php

final class GameRules
{
    private const INVITE_ALPHABET = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";

    public static function generateInviteCode(): string
    {
        $code = "";
        $lastIndex = strlen(self::INVITE_ALPHABET) - 1;
        for ($index = 0; $index < 6; $index++) {
            $code .= self::INVITE_ALPHABET[random_int(0, $lastIndex)];
        }

        return $code;
    }

    public static function normalizeInviteCode(string $code): string
    {
        return strtoupper(preg_replace("/[^A-Za-z0-9]/", "", trim($code)) ?? "");
    }

    public static function isValidInviteCode(string $code): bool
    {
        return (bool) preg_match("/^[A-Z0-9]{6}$/", self::normalizeInviteCode($code));
    }

    public static function clampInt($value, int $minimum, int $maximum): int
    {
        return max($minimum, min($maximum, (int) $value));
    }

    public static function nextTurn(int $currentIndex, int $round, int $combatantCount): array
    {
        if ($combatantCount <= 0) {
            return ["turn_index" => 0, "round" => max(1, $round)];
        }

        $nextIndex = $currentIndex + 1;
        if ($nextIndex >= $combatantCount) {
            return ["turn_index" => 0, "round" => max(1, $round) + 1];
        }

        return [
            "turn_index" => max(0, $nextIndex),
            "round" => max(1, $round),
        ];
    }

    public static function canControlCombatant(
        array $membership,
        array $combatant
    ): bool {
        if (($membership["role"] ?? "") === "dm") {
            return true;
        }

        return (int) ($membership["id_user"] ?? 0) > 0
            && (int) ($membership["id_user"] ?? 0)
                === (int) ($combatant["owner_user_id"] ?? 0);
    }

    public static function decodeJsonList($json): array
    {
        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }
}
