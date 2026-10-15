<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\GrupoAsignatura;
use Illuminate\Support\Collection;

class MatriculaService
{
    /**
     * @return list<array{0: GrupoAsignatura, 1: GrupoAsignatura}>
     */
    public function cruces(Collection $grupos): array
    {
        $items = $grupos->values();
        $cruces = [];

        for ($i = 0; $i < $items->count(); $i++) {
            for ($j = $i + 1; $j < $items->count(); $j++) {
                if ($this->seSolapan($items[$i], $items[$j])) {
                    $cruces[] = [$items[$i], $items[$j]];
                }
            }
        }

        return $cruces;
    }

    public function seSolapan(GrupoAsignatura $a, GrupoAsignatura $b): bool
    {
        $compartenDia = count(array_intersect($a->dias ?? [], $b->dias ?? [])) > 0;

        if (! $compartenDia) {
            return false;
        }

        return $this->aMinutos($a->inicio) < $this->aMinutos($b->fin)
            && $this->aMinutos($b->inicio) < $this->aMinutos($a->fin);
    }

    public function creditos(Collection $grupos): int
    {
        return (int) $grupos->sum(fn (GrupoAsignatura $grupo) => $grupo->asignatura->creditos);
    }

    public function gruposSeleccionados(Estudiante $estudiante): Collection
    {
        return $estudiante->preselecciones()
            ->with('grupo.asignatura', 'grupo.profesor')
            ->get()
            ->map(fn ($item) => $item->grupo)
            ->filter();
    }

    private function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return ($h * 60) + $m;
    }
}
