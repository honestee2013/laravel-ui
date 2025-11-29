<?php

namespace QuickerFaster\LaravelUI\Traits\DataTable;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait DataTableImageHandlerTrait
{
    
    public $field;
    public $imageUrl;
    public $recordId;
    public $croppedImage;
    public $showCropModal = false;
    
    
    /*public function openCropModal($field, $imageUrl, $recordId)
    {
        $this->field = $field;
        $this->imageUrl = $imageUrl;
        $this->recordId = $recordId;
        $this->showCropModal = true;
        
        $this->dispatch('open-crop-modal'); // browser event
    }*/


 public function openCropImageModal($field, $imgUrl, $id)
    {
        
        
        $modalId = "crop-image-modal";
        $data = [
            "modalId" => $modalId,
            "field" => $field,
            "imgUrl" => $imgUrl,
            "id" => $id,
        ];

        //@include('system.views::data-tables.modals.crop-image-modal')

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        $viewPath =  "qf::components.livewire.$UIFramework";
        $modalHtml = view("$viewPath.data-tables.modals.crop-image-modal", $data)->render();
           

        //$modalHtml = view('system.views::data-tables.modals.crop-image-modal', $data)->render();
        //$this->dispatch("open-add-relationship-modal", ['modalHtml' => $modalHtml, "modalId" => $modalId]);
        $data["modalHtml"] = $modalHtml;

        $this->dispatch("show-crop-image-modal-event", $data);
    }


        public function showCropImageModal($field, $imgUrl)
    {
        $this->dispatch('show-crop-image-modal', ['field' => $field, 'imgUrl' => $imgUrl, 'id' => $this->getId()]);
    }



public function saveCroppedImage($field, $croppedImageBase64, $id)
{
    if (!preg_match('/^data:image\/(jpg|jpeg|png);base64,/', $croppedImageBase64, $matches)) {
        throw new \Exception('Invalid image format.');
    }

    $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $croppedImageBase64));

    // Define consistent crop filename
    $cropFileName = "crops/" . strtolower($field) . '_' . $id . '.' . $extension;
    $fullCropPath = 'uploads/' . $cropFileName;

    // Step 1: If in edit mode, delete the previous crop (if any)
    if ($this->isEditMode && $this->selectedItemId) {
        $record = $this->model::find($this->selectedItemId);
        $oldCropPath = $record->{$field} ?? null;

        // Only delete if it's a crop path (optional safety check)
        if ($oldCropPath && str_starts_with($oldCropPath, 'uploads/crops/')) {
            Storage::disk('public')->delete($oldCropPath);
        }
    }

    // Step 2: Save new cropped image
    Storage::disk('public')->put($fullCropPath, $imageData);

    // Step 3: Store the new path in the model
    $this->fields[$field] = $fullCropPath;

    $this->dispatch('$refresh');
    $this->dispatch('swal:success', [
        'title' => 'Success!',
        'text' => 'Image was cropped successfully!',
        'icon' => 'success',
    ]);
}




    
    /*public function cropImage()
    {
        $this->validate([
            'croppedImage' => 'required|image|max:10240', // 10MB max
        ]);
        
        try {
            // Process and store the cropped image
            $image = Image::make($this->croppedImage->getRealPath());
            
            // Crop to desired dimensions (you can make these configurable)
            $image->crop(300, 300);
            
            // Generate filename and path
            $filename = $this->field . '_' . $this->recordId . '_' . time() . '.jpg';
            $path = 'uploads/images/' . $filename;
            
            // Save the image
            Storage::disk('public')->put($path, $image->encode('jpg', 80));
            
            // Emit event to parent component
            $this->dispatch('imageCropped', $this->field, $path);
            
            $this->dispatch('notify', [ // browser event
                'type' => 'success',
                'message' => 'Image cropped and saved successfully.'
            ]);
            
            $this->resetCropModal();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [ // browser event
                'type' => 'error',
                'message' => 'Failed to process image: ' . $e->getMessage()
            ]);
        }
    }*/
    
    public function resetCropModal()
    {
        $this->reset(['field', 'imageUrl', 'recordId', 'croppedImage', 'showCropModal']);
        $this->dispatch('close-crop-modal'); // browser event
    }
    

}