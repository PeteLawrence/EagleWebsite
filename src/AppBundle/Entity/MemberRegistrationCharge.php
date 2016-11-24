<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MemberRegistrationCharge extends \AppBundle\Entity\Charge
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MemberRegistration", inversedBy="memberRegistrationCharge")
     * @ORM\JoinColumn(name="member_registration_id", referencedColumnName="id")
     */
    private $memberRegistration;

    /**
     * Set memberRegistration
     *
     * @param \AppBundle\Entity\MemberRegistration $memberRegistration
     *
     * @return MemberRegistrationCharge
     */
    public function setMemberRegistration(\AppBundle\Entity\MemberRegistration $memberRegistration = null)
    {
        $this->memberRegistration = $memberRegistration;

        return $this;
    }

    /**
     * Get memberRegistration
     *
     * @return \AppBundle\Entity\MemberRegistration
     */
    public function getMemberRegistration()
    {
        return $this->memberRegistration;
    }
}
