<?php

namespace App\Api\V1\Transformers\MaterialAnalysis;

use DerekPhilipAu\Ceramicscalc\Models\Analysis\Analysis;

use App\Models\MaterialAnalysis;

use League\Fractal;

class MaterialUmfAnalysisTransformer extends Fractal\TransformerAbstract
{
    public function transform($analysis)
    {
        $analysis_data = array();

        if ($analysis)
        {
            $umf_analysis = [];
            foreach(Analysis::OXIDE_NAMES as $oxide_name)
            {
                $umf_oxide_name = $oxide_name.'_umf';

                if ($analysis[$umf_oxide_name] > 0)
                {
                    $umf_analysis[$oxide_name] = $analysis[$umf_oxide_name];
                }
            }
            $umf_analysis['SiO2Al2O3Ratio'] = $analysis['SiO2_Al2O3_ratio_umf'];
            $umf_analysis['R2OTotal'] = $analysis['R2O_umf'];
            $umf_analysis['ROTotal'] = $analysis['RO_umf'];
            $analysis_data['umfAnalysis'] = $umf_analysis;
        }

        return $analysis_data;
    }

}