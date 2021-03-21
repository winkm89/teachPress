<?php
/**
 * This file contains lists of macros and there replacements. This is used for bibtex imports.
 * @package teachpress\core\bibtex
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 6.0.0
 */

/**
 * This class contains lists of macros and there replacements. This is used for bibtex imports.
 * @package teachpress\core\bibtex
 * @since 6.0.0
 */
class TP_Bibtex_Macros {
    
    /**
     * Returns an array of journals (macros + full name)
     * @return array
     * @since 6.0.0
     */
    public static function journals () {
        $macros = array(
            'aj' => 'Astronomical Journal',
            'actaa' => 'Acta Astronomica',
            'araa' => 'Annual Review of Astron and Astrophys',
            'apj' => 'Astrophysical Journal',
            'apjl' => 'Astrophysical Journal, Letters',
            'apjs' => 'Astrophysical Journal, Supplement',
            'ao' => 'Applied Optics',
            'apss' => 'Astrophysics and Space Science',
            'aap' => 'Astronomy and Astrophysics',
            'aapr' => 'Astronomy and Astrophysics Reviews',
            'aaps' => 'Astronomy and Astrophysics, Supplement',
            'azh' => 'Astronomicheskii Zhurnal',
            'baas' => 'Bulletin of the AAS',
            'caa' => 'Chinese Astronomy and Astrophysics',
            'cjaa' => 'Chinese Journal of Astronomy and Astrophysics',
            'icarus' => 'Icarus',
            'jcap' => 'Journal of Cosmology and Astroparticle Physics',
            'jrasc' => 'Journal of the RAS of Canada',
            'memras' => 'Memoirs of the RAS',
            'mnras' => 'Monthly Notices of the RAS',
            'na' => 'New Astronomy',
            'nar' => 'New Astronomy Review',
            'pra' => 'Physical Review A: General Physics',
            'prb' => 'Physical Review B: Solid State',
            'prc' => 'Physical Review C',
            'prd' => 'Physical Review D',
            'pre' => 'Physical Review E',
            'prl' => 'Physical Review Letters',
            'pasa' => 'Publications of the Astron. Soc. of Australia',
            'pasp' => 'Publications of the ASP',
            'pasj' => 'Publications of the ASJ',
            'rmxaa' => 'Revista Mexicana de Astronomia y Astrofisica',
            'qjras' => 'Quarterly Journal of the RAS',
            'skytel' => 'Sky and Telescope',
            'solphys' => 'Solar Physics',
            'sovast' => 'Soviet Astronomy',
            'ssr' => 'Space Science Reviews',
            'zap' => 'Zeitschrift fuer Astrophysik',
            'nat' => 'Nature',
            'iaucirc' => 'IAU Cirulars',
            'aplett' => 'Astrophysics Letters',
            'apspr' => 'Astrophysics Space Physics Research',
            'bain' => 'Bulletin Astronomical Institute of the Netherlands',
            'fcp' => 'Fundamental Cosmic Physics',
            'gca' => 'Geochimica Cosmochimica Acta',
            'grl' => 'Geophysics Research Letters',
            'jcp' => 'Journal of Chemical Physics',
            'jgr' => 'Journal of Geophysics Research',
            'jqsrt' => 'Journal of Quantitiative Spectroscopy and Radiative Transfer',
            'memsai' => 'Mem. Societa Astronomica Italiana',
            'nphysa' => 'Nuclear Physics A',
            'physrep' => 'Physics Reports',
            'physscr' => 'Physica Scripta',
            'planss' => 'Planetary Space Science',
            'procspie' => 'Proceedings of the SPIE'
        );
        return $macros;
    }
}
