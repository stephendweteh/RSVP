<?php

namespace App\Http\Controllers;

use App\Models\SliderImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSliderController extends Controller
{
    public function index(): View
    {
        $slides = SliderImage::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.slider.index', compact('slides'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:5120'],
        ]);

        $next = (int) SliderImage::query()->max('sort_order') + 1;

        foreach ($request->file('images', []) as $file) {
            if ($file === null || ! $file->isValid()) {
                continue;
            }
            SliderImage::query()->create([
                'image_path' => $file->store('slider', 'public'),
                'sort_order' => $next++,
            ]);
        }

        return redirect()
            ->route('admin.slider.index')
            ->with('success', 'Slider image(s) uploaded.');
    }

    public function destroy(SliderImage $slider): RedirectResponse
    {
        if (filled($slider->image_path)) {
            Storage::disk('public')->delete($slider->image_path);
        }
        $slider->delete();

        return redirect()
            ->route('admin.slider.index')
            ->with('success', 'Slide removed.');
    }

    public function move(Request $request, SliderImage $slider): RedirectResponse
    {
        $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $slides = SliderImage::query()->orderBy('sort_order')->orderBy('id')->get()->values();
        $idx = $slides->search(fn (SliderImage $s): bool => $s->is($slider));

        if ($idx === false) {
            return back();
        }

        $swapIdx = $request->input('direction') === 'up' ? $idx - 1 : $idx + 1;

        if ($swapIdx < 0 || $swapIdx >= $slides->count()) {
            return back();
        }

        $a = $slides[$idx];
        $b = $slides[$swapIdx];
        $slides[$idx] = $b;
        $slides[$swapIdx] = $a;

        foreach ($slides as $i => $s) {
            $s->update(['sort_order' => $i]);
        }

        return back()->with('success', 'Order updated.');
    }
}
